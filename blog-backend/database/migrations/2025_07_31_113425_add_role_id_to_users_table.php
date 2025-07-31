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
        Schema::table('users', function (Blueprint $table) {
            // 既存のusersテーブルにrole_idカラムを追加
            $table->bigInteger('role_id')->unsigned()->after('password')->comment('ロールID');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 外部キー制約の削除
            $table->dropForeign(['role_id']);
            // カラムの削除
            $table->dropColumn('role_id');
        });
    }
};
