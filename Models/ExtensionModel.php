<?php

namespace App\GP247\Plugins\MFA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use GP247\Core\Models\AdminMenu;

class ExtensionModel extends Model
{
    public function __construct()
    {
        //
    }

    /**
     * Install extension
     * 
     * @return void
     */
    public function installExtension()
    {
        // Create two_factor_auth table
        if (!Schema::hasTable('two_factor_auth')) {
            Schema::create('two_factor_auth', function (Blueprint $table) {
                $table->id();
                $table->string('user_type'); // Polymorphic user type (model class)
                $table->string('user_id'); // User ID (supports both integer and UUID)
                $table->text('secret'); // Encrypted secret key
                $table->text('recovery_codes')->nullable(); // Encrypted recovery codes
                $table->boolean('enabled')->default(0); // Is MFA enabled
                $table->timestamp('enabled_at')->nullable(); // When MFA was enabled
                $table->timestamp('last_used_at')->nullable(); // Last time MFA was used
                $table->timestamps();

                // Index for faster lookups
                $table->index(['user_type', 'user_id']);
                $table->unique(['user_type', 'user_id']);
            });
        }

        // Ensure admin menu root exists under SECURITY group if needed
        $checkMenu = AdminMenu::where('key','MFA')->first();
        if (!$checkMenu) {
            $menuSecurity = AdminMenu::where('key', 'ADMIN_SECURITY')->first();
            AdminMenu::insert([
                'parent_id' => $menuSecurity->id,
                'title' => 'Plugins/MFA::lang.title',
                'icon' => 'fas fa-chalkboard-teacher',
                'uri' => 'route_admin::admin_mfa.index',
            ]);
        }


    }

    /**
     * Uninstall extension
     * 
     * @return void
     */
    public function uninstallExtension()
    {
        // Drop tables
        Schema::dropIfExists('two_factor_auth');

        (new AdminMenu)->where('uri', 'route_admin::admin_mfa.index')->delete();
    }
}

