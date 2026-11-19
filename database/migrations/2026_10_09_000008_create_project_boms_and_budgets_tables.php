<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_boms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('item_code')->nullable(); // Malzeme / Stok Kodu
            $table->string('item_name'); // GES Paneli, Çelik Boru 100mm, PLC Panosu, PET Reçine
            $table->string('category')->default('malzeme'); // hammadde, ekipman, insaat_malzeme, elektrik, mekanik, ambalaj
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit', 20)->default('Adet'); // Adet, Metre, Kg, Ton, Set
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('currency', 5)->default('TRY');
            $table->string('supplier')->nullable();
            $table->string('status')->default('planned'); // planned, ordered, delivered, installed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });

        Schema::create('project_budget_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('title');
            $table->string('budget_type', 10)->default('CAPEX'); // CAPEX (Yatırım) veya OPEX (Operasyonel)
            $table->string('category')->default('ekipman'); // insaat, makine, iscilik, muhendislik, lisans, lojistik, harc
            $table->decimal('planned_amount', 15, 2)->default(0);
            $table->decimal('committed_amount', 15, 2)->default(0); // Sipariş verilen
            $table->decimal('actual_spent', 15, 2)->default(0); // Ödenen / Hakediş
            $table->string('currency', 5)->default('TRY');
            $table->date('invoice_date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_budget_items');
        Schema::dropIfExists('project_boms');
    }
};
