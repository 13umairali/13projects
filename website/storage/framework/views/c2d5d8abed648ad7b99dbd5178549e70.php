<div class="space-y-6">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         role="alert" aria-live="polite"
         class="p-3 bg-success/10 border border-success/20 text-success rounded-xl text-sm flex items-center justify-between">
        <span><?php echo e(session('success')); ?></span>
        <button type="button" @click="show = false" class="text-green-500 hover:text-success">&times;</button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-ink"><?php echo e(__('Contacts')); ?></h1>
            <p class="text-sm text-muted mt-0.5"><?php echo e(number_format($totalContacts)); ?> <?php echo e(__('contacts total')); ?></p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            
            <div class="relative">
                <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="<?php echo e(__('Search contacts...')); ?>"
                       class="pl-9 pr-4 py-2 text-sm bg-surface-2 border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent placeholder-gray-400 w-64">
            </div>

            
            <select wire:model.live="selectedTag" class="text-sm bg-surface-2 border border-border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value=""><?php echo e(__('All Tags')); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <option value="<?php echo e($tag->id); ?>"><?php echo e($tag->name); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>

            
            <select wire:model.live="selectedGroup" class="text-sm bg-surface-2 border border-border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value=""><?php echo e(__('All Groups')); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $contactGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <option value="<?php echo e($group->id); ?>"><?php echo e($group->name); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>

            
            <button type="button" wire:click="openImport" class="flex items-center gap-1.5 px-3 py-2 text-sm text-muted  bg-surface-2 border border-border rounded-xl hover:bg-surface transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <?php echo e(__('Import')); ?>

            </button>

            
            <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-3 py-2 text-sm text-muted  bg-surface-2 border border-border rounded-xl hover:bg-surface transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" wire:loading.remove wire:target="exportCsv" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <svg class="w-4 h-4 animate-spin" wire:loading wire:target="exportCsv" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <?php echo e(__('Export')); ?>

            </button>

            
            <button type="button" wire:click="openForm" class="btn-primary flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <?php echo e(__('Add Contact')); ?>

            </button>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
    <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <span class="text-sm text-primary-700 dark:text-primary-300 font-medium"><?php echo e(count($selectedIds)); ?> <?php echo e(__('contact(s) selected')); ?></span>
        <div class="flex items-center gap-2 flex-wrap">
            <select wire:model.live="bulkAction" class="text-sm bg-surface-2 border border-border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value=""><?php echo e(__('Choose action...')); ?></option>
                <option value="delete"><?php echo e(__('Move to Trash')); ?></option>
                <option value="tag"><?php echo e(__('Add Tag')); ?></option>
                <option value="add_to_group"><?php echo e(__('Add to Group')); ?></option>
                <option value="remove_from_group"><?php echo e(__('Remove from Group')); ?></option>
                <option value="export"><?php echo e(__('Export Selected')); ?></option>
                <option value="activate"><?php echo e(__('Set Active')); ?></option>
                <option value="unsubscribe"><?php echo e(__('Unsubscribe')); ?></option>
            </select>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bulkAction === 'tag'): ?>
            <select wire:model="bulkTagId" class="text-sm bg-surface-2 border border-border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value=""><?php echo e(__('Select tag...')); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <option value="<?php echo e($tag->id); ?>"><?php echo e($tag->name); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bulkAction === 'add_to_group' || $bulkAction === 'remove_from_group'): ?>
            <select wire:model="bulkGroupId" class="text-sm bg-surface-2 border border-border rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value=""><?php echo e(__('Select group...')); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $contactGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <option value="<?php echo e($group->id); ?>"><?php echo e($group->name); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <button type="button" wire:click="executeBulkAction"
                    wire:confirm="<?php echo e($bulkAction === 'delete' ? 'Move ' . count($selectedIds) . ' contacts to trash?' : 'Apply action to ' . count($selectedIds) . ' contacts?'); ?>"
                    wire:loading.attr="disabled"
                    class="btn-primary px-3 py-1.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    <?php if(!$bulkAction): ?> disabled <?php endif; ?>>
                <span wire:loading.remove wire:target="executeBulkAction"><?php echo e(__('Apply')); ?></span>
                <span wire:loading wire:target="executeBulkAction" class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <?php echo e(__('Applying...')); ?>

                </span>
            </button>
            <button type="button" wire:click="$set('selectedIds', [])" class="px-3 py-1.5 text-sm text-muted hover:text-ink transition-colors">
                <?php echo e(__('Clear')); ?>

            </button>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div wire:loading.delay class="text-center py-2" aria-busy="true">
        <div class="inline-flex items-center gap-2 text-sm text-muted">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <?php echo e(__('Loading...')); ?>

        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contacts->count() > 0): ?>
    <div class="bg-surface-2 rounded-2xl border border-border overflow-hidden">
        <p class="text-xs text-muted text-center py-1 md:hidden"><?php echo e(__('Swipe to see more columns')); ?></p>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-surface border-b border-border">
                        <th class="w-12 px-4 py-3">
                            <input type="checkbox" wire:model.live="selectAll" wire:change="toggleSelectAll"
                                   aria-label="Select all contacts"
                                   class="w-4 h-4 text-primary-600 border-border rounded focus:ring-primary-500">
                        </th>
                        <th class="text-left text-xs font-semibold text-muted uppercase tracking-wider px-4 py-3 cursor-pointer hover:text-ink/80" wire:click="sortBy('first_name')">
                            <?php echo e(__('Name')); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'first_name'): ?>
                            <span class="ml-1"><?php echo e($sortDirection === 'asc' ? '&#9650;' : '&#9660;'); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="text-left text-xs font-semibold text-muted uppercase tracking-wider px-4 py-3 cursor-pointer hover:text-ink/80" wire:click="sortBy('email')">
                            <?php echo e(__('Email')); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'email'): ?>
                            <span class="ml-1"><?php echo e($sortDirection === 'asc' ? '&#9650;' : '&#9660;'); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="text-left text-xs font-semibold text-muted uppercase tracking-wider px-4 py-3 hidden lg:table-cell"><?php echo e(__('Phone')); ?></th>
                        <th class="text-left text-xs font-semibold text-muted uppercase tracking-wider px-4 py-3 hidden lg:table-cell cursor-pointer hover:text-ink/80" wire:click="sortBy('company')">
                            <?php echo e(__('Company')); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'company'): ?>
                            <span class="ml-1"><?php echo e($sortDirection === 'asc' ? '&#9650;' : '&#9660;'); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="text-left text-xs font-semibold text-muted uppercase tracking-wider px-4 py-3 cursor-pointer hover:text-ink/80" wire:click="sortBy('lead_score')">
                            <?php echo e(__('Score')); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'lead_score'): ?>
                            <span class="ml-1"><?php echo e($sortDirection === 'asc' ? '&#9650;' : '&#9660;'); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </th>
                        <th class="text-left text-xs font-semibold text-muted uppercase tracking-wider px-4 py-3 hidden xl:table-cell"><?php echo e(__('Tags')); ?></th>
                        <th class="w-12 px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <tr class="hover:bg-surface transition-colors" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'contact-'.e($contact->id).''; ?>wire:key="contact-<?php echo e($contact->id); ?>">
                        <td class="px-4 py-3">
                            <input type="checkbox" value="<?php echo e($contact->id); ?>" wire:model.live="selectedIds"
                                   aria-label="Select contact"
                                   class="w-4 h-4 text-primary-600 border-border rounded focus:ring-primary-500">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-primary-500 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                                    <?php echo e($contact->initials); ?>

                                </div>
                                <span class="text-sm font-medium text-ink whitespace-nowrap"><?php echo e($contact->full_name); ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-muted "><?php echo e($contact->email); ?></td>
                        <td class="px-4 py-3 text-sm text-muted  hidden lg:table-cell"><?php echo e($contact->phone ?? '—'); ?></td>
                        <td class="px-4 py-3 text-sm text-muted  hidden lg:table-cell"><?php echo e($contact->company ?? '—'); ?></td>
                        <td class="px-4 py-3">
                            <?php
                                $score = $contact->lead_score ?? 0;
                                $scoreLabel = $score >= 80 ? 'Hot' : ($score >= 50 ? 'Warm' : 'Cold');
                                $scoreColor = match($scoreLabel) {
                                    'Hot' => 'bg-danger/15 text-danger',
                                    'Warm' => 'bg-warning/15 text-warning',
                                    'Cold' => 'bg-info/15 text-info',
                                };
                                $barColor = match($scoreLabel) {
                                    'Hot' => 'bg-danger/100',
                                    'Warm' => 'bg-warning/100',
                                    'Cold' => 'bg-info/100',
                                };
                            ?>
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-16 h-1.5 bg-surface  rounded-full overflow-hidden">
                                    <div class="<?php echo e($barColor); ?> h-full rounded-full" style="width: <?php echo e($score); ?>%"></div>
                                </div>
                                <span class="text-xs font-semibold px-1.5 py-0.5 rounded-full <?php echo e($scoreColor); ?> whitespace-nowrap"><?php echo e($score); ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden xl:table-cell">
                            <div class="flex flex-wrap gap-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $contact->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-surface  text-muted " style="<?php echo e($tag->color ? 'background-color:' . $tag->color . '20; color:' . $tag->color : ''); ?>">
                                    <?php echo e($tag->name); ?>

                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div x-data="{
                                    open: false, menuTop: 0, menuLeft: 0,
                                    reposition() { const r = this.$refs.trigger.getBoundingClientRect(); this.menuLeft = r.right - 160; this.menuTop = r.bottom + 4; },
                                    toggle() { this.open = !this.open; if (this.open) this.$nextTick(() => this.reposition()); },
                                }"
                                 @scroll.window="open && reposition()" @resize.window="open && reposition()" @keydown.escape.window="open = false">
                                <button type="button" x-ref="trigger" @click="toggle()" class="p-1.5 text-muted/60 hover:text-ink rounded-lg hover:bg-surface" aria-label="More actions" aria-haspopup="true" :aria-expanded="open">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
                                </button>
                                <template x-teleport="body">
                                    <div x-show="open" @click.outside="open = false" x-transition
                                         role="menu"
                                         :style="`position: fixed; top: ${menuTop}px; left: ${menuLeft}px; z-index: 9999;`"
                                         class="w-40 bg-surface-2 rounded-xl shadow-lg border border-border py-1" style="display: none;">
                                        <button type="button" role="menuitem" wire:click="openForm(<?php echo e($contact->id); ?>)" @click="open = false" class="w-full text-left px-4 py-2 text-sm text-ink/80 hover:bg-surface"><?php echo e(__('Edit')); ?></button>
                                        <button type="button" role="menuitem" wire:click="deleteContact(<?php echo e($contact->id); ?>)" wire:confirm="Move this contact to trash?" @click="open = false" class="w-full text-left px-4 py-2 text-sm text-danger hover:bg-danger/10"><?php echo e(__('Move to Trash')); ?></button>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="px-4 py-3 border-t border-border">
            <?php echo e($contacts->links()); ?>

        </div>
    </div>
    <?php else: ?>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $selectedTag || $selectedGroup): ?>
        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['type' => 'search','title' => __('No contacts found'),'description' => __('No contacts match your current filters. Try adjusting your search or filter criteria.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'search','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No contacts found')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No contacts match your current filters. Try adjusting your search or filter criteria.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['type' => 'contacts','title' => __('No contacts yet'),'description' => __('Add your first contact to start building your CRM.'),'actionUrl' => '/contacts','actionLabel' => __('Add Your First Contact')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'contacts','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No contacts yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Add your first contact to start building your CRM.')),'action-url' => '/contacts','action-label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Add Your First Contact'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForm): ?>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog" aria-labelledby="contact-form-modal-title">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-surface/50" wire:click="closeForm"></div>
            <div class="relative bg-surface-2 rounded-2xl shadow-xl max-w-lg w-full p-6" x-trap="$wire.showForm">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('contacts.contact-form', ['contactId' => $editingContactId]);

$__keyOuter = $__key ?? null;

$__key = 'form-' . ($editingContactId ?? 'new');
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1603705961-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showImport): ?>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog" aria-labelledby="contact-import-modal-title">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-surface/50" wire:click="closeImport"></div>
            <div class="relative bg-surface-2 rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto" x-trap="$wire.showImport">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('contacts.contact-import', []);

$__keyOuter = $__key ?? null;

$__key = 'import';
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1603705961-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/dahimail.com/public_html/resources/views/livewire/contacts/contact-list.blade.php ENDPATH**/ ?>