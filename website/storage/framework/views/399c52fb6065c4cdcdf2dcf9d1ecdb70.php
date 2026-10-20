<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('Temp Mail'),'subtitle' => __('Manage temporary email domains and settings.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Temp Mail')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Manage temporary email domains and settings.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php
        $tabs = [
            ['id' => 'domains',  'label' => __('Domains'),  'icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9'],
            ['id' => 'settings', 'label' => __('Settings'), 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
        ];
    ?>

    <div class="space-y-6" x-data="{ activeTab: 'domains', search: '' }">

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="p-4 bg-success/10 border border-success/20 rounded-xl text-sm font-medium text-success flex items-center gap-2">
            <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?php echo e(session('success')); ?>

            <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100">&times;</button>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div x-data="{ show: true }" x-show="show"
             class="p-4 bg-danger/10 border border-danger/20 rounded-xl text-sm font-medium text-danger flex items-center gap-2">
            <svg class="w-5 h-5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?php echo e(session('error')); ?>

            <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100">&times;</button>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="panel p-1.5">
            <div class="flex flex-wrap gap-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <button @click="activeTab = '<?php echo e($tab['id']); ?>'"
                        :class="activeTab === '<?php echo e($tab['id']); ?>' ? 'bg-brand text-white shadow-soft' : 'text-muted hover:bg-surface hover:text-ink'"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($tab['icon']); ?>"/></svg>
                    <span><?php echo e($tab['label']); ?></span>
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        
        <div x-show="activeTab === 'domains'" x-transition>

            
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="panel p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-ink tracking-tight"><?php echo e($totalDomains); ?></p>
                    <p class="text-xs text-muted mt-1"><?php echo e(__('Total Domains')); ?></p>
                </div>
                <div class="panel p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-ink tracking-tight"><?php echo e($activeDomains); ?></p>
                    <p class="text-xs text-muted mt-1"><?php echo e(__('Active Domains')); ?></p>
                </div>
                <div class="panel p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-ink tracking-tight"><?php echo e($totalActiveAddresses); ?></p>
                    <p class="text-xs text-muted mt-1"><?php echo e(__('Total Active Addresses')); ?></p>
                </div>
            </div>

            
            <div class="panel p-6 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="panel-heading"><?php echo e(__('Domain Directory')); ?></p>
                        <p class="mt-2 text-sm text-muted"><?php echo e($domains->count()); ?> domain(s) found. <?php echo e($activeDomains); ?> active.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" class="btn-primary" x-data @click="$dispatch('open-modal', 'create-domain')"><?php echo e(__('Add Domain')); ?></button>
                    </div>
                </div>
                <div class="mt-5 relative">
                    <svg viewBox="0 0 24 24" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                    <input type="search" class="input-field pl-10" placeholder="<?php echo e(__('Search by domain or display name...')); ?>" x-model="search">
                </div>
            </div>

            
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $domains; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $domain): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="panel overflow-hidden" x-data="{ expanded: false }"
                     x-show="!search || <?php echo e(Js::from(strtolower($domain->domain))); ?>.includes(search.toLowerCase()) || <?php echo e(Js::from(strtolower($domain->display_name ?? ''))); ?>.includes(search.toLowerCase())"
                     x-transition>

                    
                    <div class="flex items-center justify-between gap-4 px-6 py-4">
                        <button type="button" class="flex min-w-0 flex-1 items-center gap-4 text-left" @click="expanded = !expanded">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-2/70 text-muted">
                                <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate font-semibold text-ink"><?php echo e($domain->display_name ?? $domain->domain); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($domain->status === 'active'): ?>
                                    <span class="inline-flex items-center rounded-md bg-emerald-500/10 px-2 py-0.5 text-[11px] font-medium text-emerald-600 ring-1 ring-inset ring-emerald-500/20"><?php echo e(__('Active')); ?></span>
                                    <?php elseif($domain->status === 'error'): ?>
                                    <span class="inline-flex items-center rounded-md bg-red-500/10 px-2 py-0.5 text-[11px] font-medium text-red-600 ring-1 ring-inset ring-red-500/20"><?php echo e(__('Error')); ?></span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center rounded-md bg-slate-500/10 px-2 py-0.5 text-[11px] font-medium text-slate-500 ring-1 ring-inset ring-slate-500/20"><?php echo e(__('Inactive')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="mt-1 flex flex-wrap gap-3 text-xs text-muted">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"/></svg>
                                        <?php echo e($domain->active_addresses_count ?? 0); ?> address(es)
                                    </span>
                                    <span class="inline-flex items-center rounded bg-surface-2/70 px-1.5 py-0.5 text-[10px] font-medium text-muted"><?php echo e($domain->domain); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($domain->last_synced_at): ?>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        Synced <?php echo e($domain->last_synced_at->diffForHumans()); ?>

                                    </span>
                                    <?php else: ?>
                                    <span class="text-muted/60"><?php echo e(__('Never synced')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                            <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0 text-muted transition-transform duration-200" :class="expanded && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 9l6 6 6-6"/></svg>
                        </button>

                        <form method="POST" action="<?php echo e(route('admin.temp-mail.toggle', $domain)); ?>" class="shrink-0">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition <?php echo e($domain->status === 'active' ? 'bg-brand' : 'bg-border'); ?>" title="<?php echo e($domain->status === 'active' ? 'Deactivate' : 'Activate'); ?>">
                                <span class="inline-block h-5 w-5 rounded-full bg-white shadow transition" style="transform: translateX(<?php echo e($domain->status === 'active' ? '1.25rem' : '0.125rem'); ?>)"></span>
                            </button>
                        </form>
                    </div>

                    
                    <div x-show="expanded" x-collapse>
                        <div class="border-t border-border/60 px-6 py-6">
                            <form method="POST" action="<?php echo e(route('admin.temp-mail.update', $domain)); ?>" class="space-y-6">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>

                                
                                <div class="panel p-5">
                                    <p class="panel-heading"><?php echo e(__('Domain Details')); ?></p>
                                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                        <div>
                                            <label for="domain_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Domain')); ?> <span class="text-danger">*</span></label>
                                            <input type="text" id="domain_<?php echo e($domain->id); ?>" name="domain" value="<?php echo e($domain->domain); ?>" required class="input-field" placeholder="tempmail.example.com">
                                        </div>
                                        <div>
                                            <label for="display_name_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Display Name')); ?> <span class="text-danger">*</span></label>
                                            <input type="text" id="display_name_<?php echo e($domain->id); ?>" name="display_name" value="<?php echo e($domain->display_name); ?>" required class="input-field" placeholder="TempMail Pro">
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="panel p-5">
                                    <p class="panel-heading"><?php echo e(__('IMAP Configuration')); ?></p>
                                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                        <div>
                                            <label for="imap_host_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('IMAP Host')); ?> <span class="text-danger">*</span></label>
                                            <input type="text" id="imap_host_<?php echo e($domain->id); ?>" name="imap_host" value="<?php echo e($domain->imap_host); ?>" required class="input-field" placeholder="imap.example.com">
                                        </div>
                                        <div>
                                            <label for="imap_port_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('IMAP Port')); ?> <span class="text-danger">*</span></label>
                                            <input type="number" id="imap_port_<?php echo e($domain->id); ?>" name="imap_port" value="<?php echo e($domain->imap_port); ?>" required class="input-field" placeholder="993">
                                        </div>
                                        <div>
                                            <label for="imap_username_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('IMAP Username')); ?> <span class="text-danger">*</span></label>
                                            <input type="text" id="imap_username_<?php echo e($domain->id); ?>" name="imap_username" value="<?php echo e($domain->imap_username); ?>" required class="input-field" placeholder="user@example.com">
                                        </div>
                                        <div>
                                            <label for="imap_password_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('IMAP Password')); ?></label>
                                            <div class="relative" x-data="{ showPw: false }">
                                                <input :type="showPw ? 'text' : 'password'" id="imap_password_<?php echo e($domain->id); ?>" name="imap_password" class="input-field pr-12" autocomplete="new-password"
                                                       placeholder="<?php echo e($domain->imap_password ? '••••••••' : ''); ?>">
                                                <button type="button" class="absolute inset-y-0 right-3 flex items-center text-muted/70 hover:text-ink transition" @click="showPw = !showPw">
                                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/></svg>
                                                </button>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($domain->imap_password): ?>
                                            <p class="mt-1 text-xs text-muted"><?php echo e(__('Saved (hidden). Leave blank to keep current value.')); ?></p>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div>
                                            <label for="imap_encryption_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Encryption')); ?> <span class="text-danger">*</span></label>
                                            <select id="imap_encryption_<?php echo e($domain->id); ?>" name="imap_encryption" class="input-field">
                                                <option value="ssl" <?php if($domain->imap_encryption === 'ssl'): echo 'selected'; endif; ?>>SSL</option>
                                                <option value="tls" <?php if($domain->imap_encryption === 'tls'): echo 'selected'; endif; ?>>TLS</option>
                                                <option value="none" <?php if($domain->imap_encryption === 'none'): echo 'selected'; endif; ?>>None</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="panel p-5">
                                    <p class="panel-heading"><?php echo e(__('Limits & Filters')); ?></p>
                                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                        <div>
                                            <label for="lifetime_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Default Lifetime (hours)')); ?> <span class="text-danger">*</span></label>
                                            <input type="number" id="lifetime_<?php echo e($domain->id); ?>" name="default_lifetime_hours" value="<?php echo e($domain->default_lifetime_hours); ?>" required min="1" max="8760" class="input-field" placeholder="24">
                                        </div>
                                        <div>
                                            <label for="max_addr_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Max Addresses')); ?></label>
                                            <input type="number" id="max_addr_<?php echo e($domain->id); ?>" name="max_addresses" value="<?php echo e($domain->max_addresses); ?>" min="1" class="input-field" placeholder="<?php echo e(__('Unlimited')); ?>">
                                        </div>
                                        <div class="lg:col-span-2">
                                            <label for="blocked_<?php echo e($domain->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Blocked Patterns')); ?></label>
                                            <textarea id="blocked_<?php echo e($domain->id); ?>" name="blocked_patterns" rows="2" class="input-field resize-none" placeholder="admin, support, postmaster (comma-separated)"><?php echo e(is_array($domain->blocked_patterns) ? implode(', ', $domain->blocked_patterns) : ''); ?></textarea>
                                            <p class="mt-1 text-xs text-muted"><?php echo e(__('Comma-separated local parts or keywords to block.')); ?></p>
                                        </div>
                                    </div>
                                </div>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($domain->status === 'error' && $domain->error_message): ?>
                                <div class="rounded-xl border border-danger/20 bg-danger/5 p-4">
                                    <p class="text-sm font-semibold text-danger"><?php echo e(__('Last Error')); ?></p>
                                    <p class="mt-1 text-xs text-danger/80 break-all"><?php echo e($domain->error_message); ?></p>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex flex-wrap gap-2">
                                        
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('test-form-<?php echo e($domain->id); ?>').submit();" class="btn-secondary inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            <?php echo e(__('Test Connection')); ?>

                                        </a>
                                        
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('sync-form-<?php echo e($domain->id); ?>').submit();" class="btn-secondary inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <?php echo e(__('Sync Now')); ?>

                                        </a>
                                        
                                        <button type="button" class="btn-danger inline-flex items-center gap-1.5" x-data @click="$dispatch('open-modal', 'delete-domain-<?php echo e($domain->id); ?>')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <?php echo e(__('Delete')); ?>

                                        </button>
                                    </div>
                                    <button type="submit" class="btn-primary"><?php echo e(__('Save')); ?> <?php echo e($domain->domain); ?></button>
                                </div>
                            </form>

                            
                            <form id="test-form-<?php echo e($domain->id); ?>" method="POST" action="<?php echo e(route('admin.temp-mail.test', $domain)); ?>" class="hidden"><?php echo csrf_field(); ?></form>
                            <form id="sync-form-<?php echo e($domain->id); ?>" method="POST" action="<?php echo e(route('admin.temp-mail.sync', $domain)); ?>" class="hidden"><?php echo csrf_field(); ?></form>
                        </div>
                    </div>
                </div>

                
                <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'delete-domain-'.e($domain->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'delete-domain-'.e($domain->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <form method="POST" action="<?php echo e(route('admin.temp-mail.destroy', $domain)); ?>" class="p-6 space-y-4">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <div>
                            <p class="text-lg font-semibold text-ink">Delete "<?php echo e($domain->domain); ?>"?</p>
                            <p class="mt-2 text-sm text-muted"><?php echo __('This domain and its configuration will be <strong class="text-danger">permanently removed</strong>. Active addresses must be deactivated first.'); ?></p>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                            <button type="submit" class="btn-danger"><?php echo e(__('Delete Domain')); ?></button>
                        </div>
                    </form>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27)): ?>
<?php $attributes = $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27; ?>
<?php unset($__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27)): ?>
<?php $component = $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27; ?>
<?php unset($__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27); ?>
<?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="panel p-6 text-center">
                    <p class="text-sm text-muted"><?php echo e(__('No domains found. Add your first temp mail domain to get started.')); ?></p>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div x-show="activeTab === 'settings'" x-transition>
            <form method="POST" action="<?php echo e(route('admin.temp-mail.settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Global Temp Mail Settings')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Configure default behavior for all temporary email domains.')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">

                        
                        <div>
                            <label class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-ink"><?php echo e(__('Temp Mail Enabled')); ?></p>
                                    <p class="mt-0.5 text-xs text-muted"><?php echo e(__('When disabled, users cannot create new temporary email addresses.')); ?></p>
                                </div>
                                <div class="shrink-0">
                                    <input type="hidden" name="temp_mail_global_enabled" value="0">
                                    <label class="relative inline-flex cursor-pointer">
                                        <input type="checkbox" name="temp_mail_global_enabled" value="1" class="sr-only peer" <?php if(in_array(strtolower($settings['temp_mail_global_enabled']), ['true', '1', 'yes', 'on'])): echo 'checked'; endif; ?>>
                                        <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-brand transition-colors after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow peer-checked:after:translate-x-full"></div>
                                    </label>
                                </div>
                            </label>
                        </div>

                        <div class="border-t border-border/50"></div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div>
                                <label for="setting_lifetime" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Default Lifetime (hours)')); ?></label>
                                <input type="number" id="setting_lifetime" name="temp_mail_default_lifetime_hours" value="<?php echo e($settings['temp_mail_default_lifetime_hours']); ?>" required min="1" max="8760" class="input-field" placeholder="24">
                                <p class="mt-1 text-xs text-muted"><?php echo e(__('How long new temp addresses stay active by default.')); ?></p>
                            </div>
                            <div>
                                <label for="setting_cleanup" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Auto-Cleanup After (days)')); ?></label>
                                <input type="number" id="setting_cleanup" name="temp_mail_auto_cleanup_days" value="<?php echo e($settings['temp_mail_auto_cleanup_days']); ?>" required min="1" max="365" class="input-field" placeholder="30">
                                <p class="mt-1 text-xs text-muted"><?php echo e(__('Expired addresses and their messages are permanently deleted after this many days.')); ?></p>
                            </div>
                        </div>

                        <div>
                            <label for="setting_keywords" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Blocked Keywords')); ?></label>
                            <textarea id="setting_keywords" name="temp_mail_blocked_keywords" rows="3" class="input-field resize-none" placeholder="admin, support, billing, postmaster (comma-separated)"><?php echo e($settings['temp_mail_blocked_keywords']); ?></textarea>
                            <p class="mt-1 text-xs text-muted"><?php echo e(__('Comma-separated keywords. Addresses containing these words will be rejected globally.')); ?></p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-border/50 bg-surface/50 flex justify-end">
                        <button type="submit" class="btn-primary"><?php echo e(__('Save Settings')); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'create-domain','maxWidth' => 'xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'create-domain','maxWidth' => 'xl']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <form method="POST" action="<?php echo e(route('admin.temp-mail.store')); ?>" class="p-6 space-y-5">
            <?php echo csrf_field(); ?>
            <div>
                <h3 class="text-lg font-semibold text-ink"><?php echo e(__('Add Domain')); ?></h3>
                <p class="mt-1 text-sm text-muted"><?php echo e(__('Configure a new temporary email domain with IMAP credentials.')); ?></p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label for="create_domain" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Domain')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="domain" id="create_domain" required class="input-field" placeholder="tempmail.example.com">
                </div>
                <div>
                    <label for="create_display_name" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Display Name')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="display_name" id="create_display_name" required class="input-field" placeholder="TempMail Pro">
                </div>
                <div>
                    <label for="create_imap_host" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('IMAP Host')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="imap_host" id="create_imap_host" required class="input-field" placeholder="imap.example.com">
                </div>
                <div>
                    <label for="create_imap_port" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('IMAP Port')); ?> <span class="text-danger">*</span></label>
                    <input type="number" name="imap_port" id="create_imap_port" required class="input-field" placeholder="993" value="993">
                </div>
                <div>
                    <label for="create_imap_username" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('IMAP Username')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="imap_username" id="create_imap_username" required class="input-field" placeholder="user@example.com">
                </div>
                <div>
                    <label for="create_imap_password" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('IMAP Password')); ?></label>
                    <div class="relative" x-data="{ showPw: false }">
                        <input :type="showPw ? 'text' : 'password'" name="imap_password" id="create_imap_password" class="input-field pr-12" autocomplete="new-password">
                        <button type="button" class="absolute inset-y-0 right-3 flex items-center text-muted/70 hover:text-ink transition" @click="showPw = !showPw">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="create_imap_encryption" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Encryption')); ?> <span class="text-danger">*</span></label>
                    <select name="imap_encryption" id="create_imap_encryption" class="input-field">
                        <option value="ssl" selected>SSL</option>
                        <option value="tls">TLS</option>
                        <option value="none">None</option>
                    </select>
                </div>
                <div>
                    <label for="create_lifetime" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Default Lifetime (hours)')); ?> <span class="text-danger">*</span></label>
                    <input type="number" name="default_lifetime_hours" id="create_lifetime" required min="1" max="8760" class="input-field" placeholder="24" value="24">
                </div>
                <div>
                    <label for="create_max_addresses" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Max Addresses')); ?></label>
                    <input type="number" name="max_addresses" id="create_max_addresses" min="1" class="input-field" placeholder="<?php echo e(__('Unlimited')); ?>">
                </div>
                <div>
                    <label for="create_blocked_patterns" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Blocked Patterns')); ?></label>
                    <input type="text" name="blocked_patterns" id="create_blocked_patterns" class="input-field" placeholder="admin, support (comma-separated)">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                <button type="submit" class="btn-primary"><?php echo e(__('Create Domain')); ?></button>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27)): ?>
<?php $attributes = $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27; ?>
<?php unset($__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27)): ?>
<?php $component = $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27; ?>
<?php unset($__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3)): ?>
<?php $attributes = $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3; ?>
<?php unset($__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc8c9fd5d7827a77a31381de67195f0c3)): ?>
<?php $component = $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3; ?>
<?php unset($__componentOriginalc8c9fd5d7827a77a31381de67195f0c3); ?>
<?php endif; ?>
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/temp-mail/index.blade.php ENDPATH**/ ?>