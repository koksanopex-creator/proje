<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationMatrixService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CommitteeController extends Controller
{
    protected function checkViewPermission()
    {
        $u = auth()->user();
        if (!$u || (!$u->isDeveloper() && !$u->hasRole('admin') && !$u->hasPermission('manage_committees') && !$u->hasPermission('view_committees'))) {
            abort(403, 'Bu sayfayı görüntüleme yetkiniz bulunmamaktadır.');
        }
    }

    protected function checkManagePermission()
    {
        $u = auth()->user();
        if (!$u || (!$u->isDeveloper() && !$u->hasRole('admin') && !$u->hasPermission('manage_committees'))) {
            abort(403, 'Bu işlem için Kurul ve Komiteleri Yönetme yetkiniz bulunmamaktadır.');
        }
    }

    public function index()
    {
        $this->checkViewPermission();

        $committees = Committee::with(['members.user', 'creator'])
            ->withCount('activeMembers')
            ->orderBy('name')
            ->get();

        // İstatistikler (KPI Summary)
        $totalCommittees = $committees->count();
        $activeCommittees = $committees->where('is_active', true)->count();
        $totalMembersAssigned = CommitteeMember::where('is_active', true)->count();
        $totalUniqueUsers = CommitteeMember::where('is_active', true)->distinct('user_id')->count('user_id');
        $totalVotingPower = CommitteeMember::where('is_active', true)->sum('voting_weight');
        $pendingApprovals = \App\Models\ProjectApprovalRequest::where('target_approver_type', 'committee')->where('status', 'pending')->count();

        $stats = [
            'total' => $totalCommittees,
            'active' => $activeCommittees,
            'total_seats' => $totalMembersAssigned,
            'unique_users' => $totalUniqueUsers,
            'voting_power' => $totalVotingPower,
            'pending_approvals' => $pendingApprovals,
        ];

        return view('admin.committees.index', compact('committees', 'stats'));
    }

    /**
     * Kurula Üye Ekleme Modalı için Asenkron, Sayfalamalı ve Anlık Kullanıcı Arama
     */
    public function searchUsers(Request $request)
    {
        $this->checkViewPermission();

        $query = trim($request->input('q', ''));
        $page = max(1, (int)$request->input('page', 1));
        $committeeId = $request->input('committee_id');
        $perPage = 25; // 20-30 arası alfabetik sayfalama

        $alreadyMemberIds = [];
        if ($committeeId) {
            $alreadyMemberIds = CommitteeMember::where('committee_id', $committeeId)
                ->pluck('user_id')
                ->toArray();
        }

        $usersQuery = User::where('is_active', true)
            ->select(['id', 'name', 'email', 'unvan', 'sicil_no', 'bolum_id'])
            ->with('bolum:id,ad');

        if ($query !== '') {
            $cleanQ = preg_replace('/\s+/', ' ', $query);
            $terms = array_values(array_filter(explode(' ', $cleanQ)));
            if (!empty($terms)) {
                $usersQuery->where(function ($mainSub) use ($terms, $cleanQ) {
                    $mainSub->where(function ($allTermsSub) use ($terms) {
                        foreach ($terms as $term) {
                            $allTermsSub->where(function ($singleTermSub) use ($term) {
                                $singleTermSub->where('name', 'like', "%{$term}%")
                                    ->orWhere('email', 'like', "%{$term}%")
                                    ->orWhere('sicil_no', 'like', "%{$term}%")
                                    ->orWhere('unvan', 'like', "%{$term}%");
                            });
                        }
                    })->orWhere('name', 'like', "%{$cleanQ}%");
                });
            }
        }

        $usersQuery->orderBy('name', 'asc');

        $paginator = $usersQuery->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($u) use ($alreadyMemberIds) {
            $isAlreadyMember = in_array($u->id, $alreadyMemberIds);
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'unvan' => $u->unvan ?? '',
                'sicil_no' => $u->sicil_no ?? '',
                'bolum_name' => $u->bolum?->ad ?? '',
                'initials' => mb_strtoupper(mb_substr($u->name, 0, 1)),
                'already_member' => $isAlreadyMember,
            ];
        });

        return response()->json([
            'success' => true,
            'users' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
        ]);
    }

    public function store(Request $request)
    {
        $this->checkManagePermission();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'voting_type' => 'required|string|in:unanimous,majority,single_any,quorum_percentage',
            'quorum_percentage' => 'required|integer|min:1|max:100',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'authorities' => 'nullable|array',
        ]);

        $slugBase = Str::slug($validated['name']);
        $slug = $slugBase;
        $counter = 1;
        while (Committee::where('slug', $slug)->exists()) {
            $slug = "{$slugBase}-{$counter}";
            $counter++;
        }

        $committee = Committee::create(array_merge($validated, [
            'slug' => $slug,
            'color' => $validated['color'] ?? '#0284c7',
            'icon' => $validated['icon'] ?? '🏛️',
            'authorities' => $request->input('authorities', []),
            'is_active' => true,
            'created_by' => auth()->id(),
        ]));

        AuditLogService::log(
            'created',
            "Yeni Kurul / Komite oluşturuldu: '{$committee->name}' ({$committee->voting_type_label})",
            'Committee',
            $committee->id,
            null,
            null,
            $committee->toArray()
        );

        return redirect()->route('admin.committees.index')
            ->with('success', "'{$committee->name}' kurulu başarıyla oluşturuldu.");
    }

    public function update(Request $request, $id)
    {
        $this->checkManagePermission();

        $committee = Committee::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'voting_type' => 'required|string|in:unanimous,majority,single_any,quorum_percentage',
            'quorum_percentage' => 'required|integer|min:1|max:100',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'authorities' => 'nullable|array',
        ]);

        $oldValues = $committee->toArray();
        $committee->update(array_merge($validated, [
            'authorities' => $request->input('authorities', []),
        ]));

        AuditLogService::log(
            'updated',
            "Kurul / Komite güncellendi: '{$committee->name}'",
            'Committee',
            $committee->id,
            null,
            $oldValues,
            $committee->toArray()
        );

        return redirect()->route('admin.committees.index')
            ->with('success', "'{$committee->name}' kurulu güncellendi.");
    }

    public function destroy($id)
    {
        $this->checkManagePermission();

        $committee = Committee::findOrFail($id);
        $name = $committee->name;
        $committee->delete();

        AuditLogService::log(
            'deleted',
            "Kurul / Komite silindi: '{$name}'",
            'Committee',
            $id
        );

        return redirect()->route('admin.committees.index')
            ->with('success', "'{$name}' kurulu silindi.");
    }

    public function toggleActive($id)
    {
        $this->checkManagePermission();

        $committee = Committee::findOrFail($id);
        $committee->is_active = !$committee->is_active;
        $committee->save();

        $statusText = $committee->is_active ? 'Aktife Alındı' : 'Devre Dışı Bırakıldı';

        AuditLogService::log(
            'status_changed',
            "Kurul durumu değiştirildi: '{$committee->name}' - {$statusText}",
            'Committee',
            $committee->id,
            null,
            null,
            ['is_active' => $committee->is_active]
        );

        return response()->json([
            'success' => true,
            'is_active' => $committee->is_active,
            'message' => "'{$committee->name}' kurulu {$statusText}.",
        ]);
    }

    public function addMember(Request $request, $id)
    {
        $this->checkManagePermission();

        $committee = Committee::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'member_role' => 'required|string|in:baskan,uye,raportor,yedek',
            'voting_weight' => 'required|numeric|min:0.1|max:10.0',
        ]);

        $member = CommitteeMember::updateOrCreate(
            [
                'committee_id' => $committee->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'member_role' => $validated['member_role'],
                'voting_weight' => $validated['voting_weight'],
                'is_active' => true,
            ]
        );

        $user = User::find($validated['user_id']);

        AuditLogService::log(
            'member_added',
            "**{$user->name}** kullanıcısı **{$committee->name}** kuruluna **{$member->role_label}** olarak atandı.",
            'CommitteeMember',
            $member->id,
            null,
            null,
            $member->toArray()
        );

        // Bildirim Matrisi Entegrasyonu (Zil ve E-Posta)
        $sendBell = $request->has('send_bell') ? $request->boolean('send_bell') : true;
        $sendMail = $request->has('send_mail') ? $request->boolean('send_mail') : true;

        if ($sendBell || $sendMail) {
            try {
                app(NotificationMatrixService::class)->dispatch(
                    'kurula_uye_atandi',
                    [
                        'user_name' => $user->name,
                        'committee_name' => $committee->name,
                        'member_role' => $member->role_label,
                        'voting_weight' => number_format($member->voting_weight, 1) . 'x',
                        'assigned_by' => auth()->user()?->name ?? 'Sistem Yöneticisi',
                    ],
                    route('admin.committees.index'),
                    $user->id
                );
            } catch (\Exception $e) {
                Log::warning("Kurul üye atama bildirimi gönderilemedi: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.committees.index')
            ->with('success', "'{$user->name}' başarıyla kurula eklendi.");
    }

    public function removeMember(Request $request, $committeeId, $memberId)
    {
        $this->checkManagePermission();

        $member = CommitteeMember::where('committee_id', $committeeId)->findOrFail($memberId);
        $userId = $member->user_id;
        $userName = $member->user?->name ?? 'Kullanıcı';
        $committeeName = $member->committee?->name ?? 'Kurul';

        $member->delete();

        AuditLogService::log(
            'member_removed',
            "**{$userName}**, **{$committeeName}** kurulundan çıkarıldı.",
            'CommitteeMember',
            $memberId
        );

        // Bildirim Matrisi Entegrasyonu (Çıkarılma Bildirimi)
        $sendBell = $request->has('send_bell') ? $request->boolean('send_bell') : true;
        $sendMail = $request->has('send_mail') ? $request->boolean('send_mail') : true;

        if (($sendBell || $sendMail) && $userId) {
            try {
                app(NotificationMatrixService::class)->dispatch(
                    'kuruldan_uye_cikarildi',
                    [
                        'user_name' => $userName,
                        'committee_name' => $committeeName,
                        'removed_by' => auth()->user()?->name ?? 'Sistem Yöneticisi',
                    ],
                    route('admin.committees.index'),
                    $userId
                );
            } catch (\Exception $e) {
                Log::warning("Kurul üye çıkarma bildirimi gönderilemedi: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.committees.index')
            ->with('success', "'{$userName}' kuruldan çıkarıldı.");
    }

    public function toggleMember($committeeId, $memberId)
    {
        $this->checkManagePermission();

        $member = CommitteeMember::where('committee_id', $committeeId)->findOrFail($memberId);
        $member->is_active = !$member->is_active;
        $member->save();

        return response()->json([
            'success' => true,
            'is_active' => $member->is_active,
            'message' => 'Üyelik durumu güncellendi.',
        ]);
    }
}
