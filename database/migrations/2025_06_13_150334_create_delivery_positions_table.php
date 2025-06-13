<?php
// ==============================================
// FILE 5: create_delivery_positions_table.php (Optional - to track your deliveries)
// ==============================================
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
        Schema::create('delivery_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('order_id')->nullable(); // Link to your orders table
            $table->string('pos_barcode')->unique();
            $table->string('pos_reference')->nullable();
            $table->string('order_number')->nullable();
            $table->enum('status', ['created', 'validated', 'in_transit', 'delivered', 'cancelled'])->default('created');
            $table->json('fparcel_data')->nullable(); // Store FParcel API response
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->decimal('cod_amount', 10, 2)->nullable(); // Cash on Delivery
            $table->integer('nb_pieces')->default(1);
            $table->date('pickup_date')->nullable();
            $table->text('content_description')->nullable();
            $table->json('sender_info')->nullable();
            $table->json('recipient_info')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'status']);
            $table->index(['admin_id', 'order_id']);
            $table->index('pos_barcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_positions');
    }
};
