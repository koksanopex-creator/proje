<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DelegationService;

class CheckExpiredDelegationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delegations:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Süresi dolan kurumsal vekaletleri otomatik sonlandırır, denetim loglarını yazar ve bildirim matrisine göre zil/e-posta iletir.';

    /**
     * Execute the console command.
     */
    public function handle(DelegationService $delegationService): int
    {
        $this->info('Kurumsal vekalet süre kontrolleri başlatılıyor...');

        $processedCount = $delegationService->checkAndNotifyExpiredDelegations();

        $this->info("İşlem tamamlandı: Toplam {$processedCount} adet süresi dolan vekalet sonlandırıldı ve bildirimleri gönderildi.");

        return Command::SUCCESS;
    }
}
