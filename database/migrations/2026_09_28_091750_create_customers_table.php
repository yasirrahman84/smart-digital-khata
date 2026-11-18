Schema::create('customers', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->constrained()->onDelete('cascade');

    $table->string('name');
    $table->string('phone')->nullable();
    $table->text('address')->nullable();
    $table->decimal('opening_balance', 10, 2)->default(0);

    $table->timestamps();
});