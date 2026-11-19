<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use App\Services\UserSyncService;

class SyncCentralUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'koksan:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merkezi Yönetim Sistemi (Merkezi API / DB) kullanıcılarını koksan_pms sistemine senkronize eder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== MERKEZİ KULLANICI SENKRONİZASYONU BAŞLATILIYOR ===");

        $result = UserSyncService::syncFromCentral();

        if ($result['success']) {
            $this->info("✓ " . $result['message']);
            $this->table(
                ['Yeni Eklenen', 'Güncellenen', 'Toplam Sistem Kullanıcısı'],
                [[$result['added'], $result['updated'], $result['total']]]
            );
            return SymfonyCommand::SUCCESS;
        }

        $this->error("✕ " . $result['message']);
        return SymfonyCommand::FAILURE;
    }
}
