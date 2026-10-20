<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('Currencies'),'subtitle' => __('Manage available currencies and exchange rates.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Currencies')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Manage available currencies and exchange rates.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">

        
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="panel p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-ink tracking-tight"><?php echo e($totalCount); ?></p>
                <p class="text-xs text-muted mt-1"><?php echo e(__('Total Currencies')); ?></p>
            </div>
            <div class="panel p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-ink tracking-tight"><?php echo e($activeCount); ?></p>
                <p class="text-xs text-muted mt-1"><?php echo e(__('Active Currencies')); ?></p>
            </div>
            <div class="panel p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M4 20L21 3"/><path d="M21 16v5h-5"/><path d="M15 15l6 6"/><path d="M4 4l5 5"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-ink tracking-tight"><?php echo e($totalCount - $activeCount); ?></p>
                <p class="text-xs text-muted mt-1"><?php echo e(__('Inactive Currencies')); ?></p>
            </div>
        </div>

        
        <div class="panel p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="panel-heading"><?php echo e(__('Currencies')); ?></p>
                    <p class="mt-2 text-sm text-muted"><?php echo e(__('Manage the currencies and exchange rates for your application.')); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-primary" x-data @click="$dispatch('open-modal', 'create-currency')"><?php echo e(__('Add Currency')); ?></button>
                </div>
            </div>

            <form method="get" action="<?php echo e(route('admin.currencies.index')); ?>" class="filter-form mt-6 grid gap-4 lg:grid-cols-[1.4fr_1fr_auto] sm:grid-cols-2">
                <div>
                    <label class="sr-only" for="search"><?php echo e(__('Search')); ?></label>
                    <input id="search" name="search" value="<?php echo e(request('search')); ?>" class="input-field" placeholder="<?php echo e(__('Search by name, code, or symbol')); ?>">
                </div>
                <div>
                    <label class="sr-only" for="status"><?php echo e(__('Status')); ?></label>
                    <select id="status" name="status" class="input-field">
                        <option value=""><?php echo e(__('All statuses')); ?></option>
                        <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>><?php echo e(__('Active')); ?></option>
                        <option value="inactive" <?php if(request('status') === 'inactive'): echo 'selected'; endif; ?>><?php echo e(__('Inactive')); ?></option>
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <button class="btn-secondary" type="submit"><?php echo e(__('Filter')); ?></button>
                    <a href="<?php echo e(route('admin.currencies.index')); ?>" class="btn-secondary"><?php echo e(__('Reset')); ?></a>
                </div>
            </form>
        </div>

        
        <div class="panel p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="panel-heading"><?php echo e(__('Currency Directory')); ?></p>
                    <p class="mt-2 text-sm text-muted"><?php echo e(__('Showing')); ?> <?php echo e($currencies->count()); ?> <?php echo e(__('of')); ?> <?php echo e($currencies->total()); ?> <?php echo e(__('currencies.')); ?></p>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-[0.2em] text-muted">
                        <tr>
                            <th class="pb-3"><?php echo e(__('Symbol')); ?></th>
                            <th class="pb-3"><?php echo e(__('Code')); ?></th>
                            <th class="pb-3"><?php echo e(__('Name')); ?></th>
                            <th class="pb-3"><?php echo e(__('Exchange Rate')); ?></th>
                            <th class="pb-3"><?php echo e(__('Position')); ?></th>
                            <th class="pb-3"><?php echo e(__('Decimal')); ?></th>
                            <th class="pb-3"><?php echo e(__('Status')); ?></th>
                            <th class="pb-3"><?php echo e(__('Default')); ?></th>
                            <th class="pb-3 text-right"><?php echo e(__('Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr>
                            <td class="py-4">
                                <span class="text-lg font-semibold text-ink"><?php echo e($currency->symbol); ?></span>
                            </td>
                            <td class="py-4">
                                <span class="text-xs font-mono text-muted bg-surface px-2 py-0.5 rounded"><?php echo e($currency->code); ?></span>
                            </td>
                            <td class="py-4">
                                <span class="font-semibold text-ink"><?php echo e($currency->name); ?></span>
                            </td>
                            <td class="py-4 text-sm text-ink font-mono"><?php echo e(number_format((float) $currency->exchange_rate, 6)); ?></td>
                            <td class="py-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currency->symbol_position === 'before'): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-full"><?php echo e(__('Before')); ?></span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-full"><?php echo e(__('After')); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="py-4 text-sm text-ink">
                                <span class="text-xs text-muted"><?php echo e($currency->decimal_digits); ?> <?php echo e(__('digits')); ?></span>
                            </td>
                            <td class="py-4">
                                <form method="POST" action="<?php echo e(route('admin.currencies.toggle', $currency)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="inline-flex items-center gap-1.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currency->is_active): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-success/15 text-success rounded-full"><?php echo e(__('Active')); ?></span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-surface text-ink/80 rounded-full"><?php echo e(__('Inactive')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </button>
                                </form>
                            </td>
                            <td class="py-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currency->is_default): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-brand/15 text-brand rounded-full"><?php echo e(__('Default')); ?></span>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo e(route('admin.currencies.default', $currency)); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="text-xs text-muted hover:text-brand transition"><?php echo e(__('Set Default')); ?></button>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" class="btn-secondary" x-data @click="$dispatch('open-modal', 'edit-currency-<?php echo e($currency->id); ?>')"><?php echo e(__('Edit')); ?></button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($currency->is_default)): ?>
                                    <button type="button" class="btn-danger" x-data @click="$dispatch('open-modal', 'delete-currency-<?php echo e($currency->id); ?>')"><?php echo e(__('Delete')); ?></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        
                        <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'edit-currency-'.e($currency->id).'','maxWidth' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit-currency-'.e($currency->id).'','maxWidth' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <form method="POST" action="<?php echo e(route('admin.currencies.update', $currency)); ?>" class="p-6 space-y-5">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <div>
                                    <h3 class="text-lg font-semibold text-ink"><?php echo e(__('Edit Currency')); ?></h3>
                                    <p class="mt-1 text-sm text-muted">Update the details for "<?php echo e($currency->name); ?>".</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="edit_name_<?php echo e($currency->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Name')); ?> <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="edit_name_<?php echo e($currency->id); ?>" value="<?php echo e($currency->name); ?>" required class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_code_<?php echo e($currency->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Code')); ?> <span class="text-danger">*</span></label>
                                        <input type="text" name="code" id="edit_code_<?php echo e($currency->id); ?>" value="<?php echo e($currency->code); ?>" required maxlength="5" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_symbol_<?php echo e($currency->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Symbol')); ?> <span class="text-danger">*</span></label>
                                        <input type="text" name="symbol" id="edit_symbol_<?php echo e($currency->id); ?>" value="<?php echo e($currency->symbol); ?>" required maxlength="10" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_position_<?php echo e($currency->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Symbol Position')); ?> <span class="text-danger">*</span></label>
                                        <select name="symbol_position" id="edit_position_<?php echo e($currency->id); ?>" class="input-field">
                                            <option value="before" <?php if($currency->symbol_position === 'before'): echo 'selected'; endif; ?>><?php echo e(__('Before ($100)')); ?></option>
                                            <option value="after" <?php if($currency->symbol_position === 'after'): echo 'selected'; endif; ?>><?php echo e(__('After (100$)')); ?></option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="edit_decimal_sep_<?php echo e($currency->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Decimal Separator')); ?> <span class="text-danger">*</span></label>
                                        <input type="text" name="decimal_separator" id="edit_decimal_sep_<?php echo e($currency->id); ?>" value="<?php echo e($currency->decimal_separator); ?>" required maxlength="5" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_thousand_sep_<?php echo e($currency->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Thousand Separator')); ?> <span class="text-danger">*</span></label>
                                        <input type="text" name="thousand_separator" id="edit_thousand_sep_<?php echo e($currency->id); ?>" value="<?php echo e($currency->thousand_separator); ?>" required maxlength="5" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_decimal_digits_<?php echo e($currency->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Decimal Digits')); ?> <span class="text-danger">*</span></label>
                                        <input type="number" name="decimal_digits" id="edit_decimal_digits_<?php echo e($currency->id); ?>" value="<?php echo e($currency->decimal_digits); ?>" required min="0" max="4" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_exchange_rate_<?php echo e($currency->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Exchange Rate')); ?> <span class="text-danger">*</span></label>
                                        <input type="number" name="exchange_rate" id="edit_exchange_rate_<?php echo e($currency->id); ?>" value="<?php echo e($currency->exchange_rate); ?>" required step="0.000001" min="0" class="input-field">
                                    </div>
                                </div>
                                <div class="flex items-center gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" <?php if($currency->is_active): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-border bg-surface-2/80 text-brand focus:ring-brand/40">
                                        <span class="text-sm text-ink"><?php echo e(__('Active')); ?></span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="hidden" name="is_default" value="0">
                                        <input type="checkbox" name="is_default" value="1" <?php if($currency->is_default): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-border bg-surface-2/80 text-brand focus:ring-brand/40">
                                        <span class="text-sm text-ink"><?php echo e(__('Default')); ?></span>
                                    </label>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                                    <button type="submit" class="btn-primary"><?php echo e(__('Update Currency')); ?></button>
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

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($currency->is_default)): ?>
                        <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'delete-currency-'.e($currency->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'delete-currency-'.e($currency->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <form method="POST" action="<?php echo e(route('admin.currencies.destroy', $currency)); ?>" class="p-6 space-y-4">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <div>
                                    <p class="text-lg font-semibold text-ink">Delete "<?php echo e($currency->name); ?>" (<?php echo e($currency->code); ?>)?</p>
                                    <p class="mt-2 text-sm text-muted"><?php echo __('This currency will be <strong class="text-danger">permanently removed</strong>. This action cannot be undone.'); ?></p>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                                    <button type="submit" class="btn-danger"><?php echo e(__('Delete Currency')); ?></button>
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
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="9" class="py-6 text-center text-sm text-muted"><?php echo e(__('No currencies found.')); ?></td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currencies->hasPages()): ?>
            <div class="mt-4 border-t border-border/60 pt-4">
                <?php echo e($currencies->withQueryString()->links()); ?>

            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'create-currency','maxWidth' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'create-currency','maxWidth' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <form method="POST" action="<?php echo e(route('admin.currencies.store')); ?>" class="p-6 space-y-5">
            <?php echo csrf_field(); ?>
            <div>
                <h3 class="text-lg font-semibold text-ink"><?php echo e(__('Add Currency')); ?></h3>
                <p class="mt-1 text-sm text-muted"><?php echo e(__('Add a new currency to your application.')); ?></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_name" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Name')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="create_name" required class="input-field" placeholder="<?php echo e(__('US Dollar')); ?>">
                </div>
                <div>
                    <label for="create_code" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Code')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="create_code" required maxlength="5" class="input-field" placeholder="USD">
                </div>
                <div>
                    <label for="create_symbol" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Symbol')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="symbol" id="create_symbol" required maxlength="10" class="input-field" placeholder="$">
                </div>
                <div>
                    <label for="create_position" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Symbol Position')); ?> <span class="text-danger">*</span></label>
                    <select name="symbol_position" id="create_position" class="input-field">
                        <option value="before"><?php echo e(__('Before ($100)')); ?></option>
                        <option value="after"><?php echo e(__('After (100$)')); ?></option>
                    </select>
                </div>
                <div>
                    <label for="create_decimal_sep" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Decimal Separator')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="decimal_separator" id="create_decimal_sep" required maxlength="5" class="input-field" placeholder="." value=".">
                </div>
                <div>
                    <label for="create_thousand_sep" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Thousand Separator')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="thousand_separator" id="create_thousand_sep" required maxlength="5" class="input-field" placeholder="," value=",">
                </div>
                <div>
                    <label for="create_decimal_digits" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Decimal Digits')); ?> <span class="text-danger">*</span></label>
                    <input type="number" name="decimal_digits" id="create_decimal_digits" required min="0" max="4" class="input-field" value="2">
                </div>
                <div>
                    <label for="create_exchange_rate" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Exchange Rate')); ?> <span class="text-danger">*</span></label>
                    <input type="number" name="exchange_rate" id="create_exchange_rate" required step="0.000001" min="0" class="input-field" value="1.000000">
                </div>
            </div>
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-border bg-surface-2/80 text-brand focus:ring-brand/40">
                    <span class="text-sm text-ink"><?php echo e(__('Active')); ?></span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" class="h-4 w-4 rounded border-border bg-surface-2/80 text-brand focus:ring-brand/40">
                    <span class="text-sm text-ink"><?php echo e(__('Default')); ?></span>
                </label>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                <button type="submit" class="btn-primary"><?php echo e(__('Create Currency')); ?></button>
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
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/currencies/index.blade.php ENDPATH**/ ?>