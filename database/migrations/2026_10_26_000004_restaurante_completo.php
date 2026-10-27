<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
 Schema::table('users',fn(Blueprint $t)=>$t->string('phone',22)->nullable());
 Schema::create('products',function(Blueprint $t){$t->id();$t->string('name');$t->string('category');$t->text('description');$t->unsignedInteger('price');$t->string('emoji',40)->default('🍽️');$t->boolean('active')->default(true);});
 Schema::create('orders',function(Blueprint $t){$t->string('id')->primary();$t->foreignId('user_id')->constrained();$t->string('created');$t->string('status');$t->string('payment');$t->unsignedBigInteger('total');$t->text('items');});
 Schema::create('reservations',function(Blueprint $t){$t->string('id')->primary();$t->foreignId('user_id')->constrained();$t->foreignId('mesa_id')->constrained('mesas');$t->string('slot')->index();$t->unsignedInteger('guests');$t->string('status');$t->string('created');});
 Schema::create('restaurant_settings',function(Blueprint $t){$t->id();$t->text('data');});
 }
 public function down(): void {foreach(['restaurant_settings','reservations','orders','products'] as $t)Schema::dropIfExists($t);Schema::table('users',fn(Blueprint $t)=>$t->dropColumn('phone'));}
};
