<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Services\AuditLogService;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all();
        $siteTitle = SystemSetting::get('site_title', 'Köksan Enterprise PPM');
        $siteLogo = SystemSetting::get('site_logo', 'assets/images/koksan_logo.png');
        $companyName = SystemSetting::get('company_name', 'Köksan Pet ve Plastik Ambalaj Sanayi ve Ticaret A.Ş.');
        $currencyDefault = SystemSetting::get('currency_default', 'TRY');
        $ehsGateEnabled = SystemSetting::get('ehs_gatekeeper_enabled', '1');

        return view('admin.settings', compact(
            'settings', 'siteTitle', 'siteLogo', 'companyName', 'currencyDefault', 'ehsGateEnabled'
        ));
    }

    public function update(Request $request)
    {
        $changes = [];

        // Logo yükleme
        if ($request->hasFile('logo')) {
            $request->validate([
                'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            ]);

            $path = $request->file('logo')->store('branding', 'public');
            SystemSetting::set('site_logo', 'storage/' . $path, 'image', 'branding');
            $changes[] = 'Kurumsal logo yenilendi';

            AuditLogService::log(
                action: 'updated',
                description: "Sistem kurumsal logosu güncellendi.",
                entityType: 'SystemSetting'
            );
        }

        if ($request->has('site_title') && $request->filled('site_title')) {
            $oldTitle = SystemSetting::get('site_title');
            if ($oldTitle !== $request->site_title) {
                SystemSetting::set('site_title', $request->site_title);
                $changes[] = "Portföy başlığı: **{$request->site_title}**";
            }
        }

        if ($request->has('currency_default') && $request->filled('currency_default')) {
            SystemSetting::set('currency_default', $request->currency_default);
            $changes[] = "Varsayılan para birimi: **{$request->currency_default}**";
        }

        if ($request->has('ehs_gatekeeper_enabled')) {
            $ehsVal = $request->boolean('ehs_gatekeeper_enabled') ? '1' : '0';
            SystemSetting::set('ehs_gatekeeper_enabled', $ehsVal);
            $changes[] = "İSG Kapı Kilidi politikası (" . ($ehsVal === '1' ? 'Aktif' : 'Pasif') . ")";
        }

        if (!empty($changes)) {
            AuditLogService::log(
                action: 'updated',
                description: "Sistem ayarları güncellendi (" . implode(', ', $changes) . ").",
                entityType: 'SystemSetting'
            );
        }

        return back()->with('success', "Sistem ve kurumsal ayarlar başarıyla kaydedildi.");
    }
}