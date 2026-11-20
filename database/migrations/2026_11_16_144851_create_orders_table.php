<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_code')->unique();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('size', 20);
    $table->string('color', 50);
    $table->integer('quantity');
    $table->decimal('total_price', 12, 2);
    $table->string('design_file')->nullable();
    $table->unsignedTinyInteger('revision_count')->default(0);
    $table->enum('payment_status', ['belum_dp', 'dp_50', 'lunas'])->default('belum_dp');
    $table->enum('production_status', ['konsultasi', 'desain_disetujui', 'proses_sablon', 'selesai', 'diambil'])->default('konsultasi');
    $table->enum('order_source', ['manual_web', 'whatsapp_bot'])->default('manual_web');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
