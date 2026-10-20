<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('Languages'),'subtitle' => __('Manage available languages and localization settings.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Languages')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Manage available languages and localization settings.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">

        
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="panel p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-ink tracking-tight"><?php echo e($totalCount); ?></p>
                <p class="text-xs text-muted mt-1"><?php echo e(__('Total Languages')); ?></p>
            </div>
            <div class="panel p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-ink tracking-tight"><?php echo e($activeCount); ?></p>
                <p class="text-xs text-muted mt-1"><?php echo e(__('Active Languages')); ?></p>
            </div>
            <div class="panel p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-ink tracking-tight"><?php echo e($totalCount - $activeCount); ?></p>
                <p class="text-xs text-muted mt-1"><?php echo e(__('Inactive Languages')); ?></p>
            </div>
        </div>

        
        <div class="panel p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="panel-heading"><?php echo e(__('Languages')); ?></p>
                    <p class="mt-2 text-sm text-muted"><?php echo e(__('Manage the languages available for your application.')); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="btn-primary" x-data @click="$dispatch('open-modal', 'create-language')"><?php echo e(__('Add Language')); ?></button>
                </div>
            </div>

            <form method="get" action="<?php echo e(route('admin.languages.index')); ?>" class="filter-form mt-6 grid gap-4 lg:grid-cols-[1.4fr_1fr_auto] sm:grid-cols-2">
                <div>
                    <label class="sr-only" for="search"><?php echo e(__('Search')); ?></label>
                    <input id="search" name="search" value="<?php echo e(request('search')); ?>" class="input-field" placeholder="<?php echo e(__('Search by name, code, or native name')); ?>">
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
                    <a href="<?php echo e(route('admin.languages.index')); ?>" class="btn-secondary"><?php echo e(__('Reset')); ?></a>
                </div>
            </form>
        </div>

        
        <div class="panel p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="panel-heading"><?php echo e(__('Language Directory')); ?></p>
                    <p class="mt-2 text-sm text-muted"><?php echo e(__('Showing')); ?> <?php echo e($languages->count()); ?> <?php echo e(__('of')); ?> <?php echo e($languages->total()); ?> <?php echo e(__('languages.')); ?></p>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-[0.2em] text-muted">
                        <tr>
                            <th class="pb-3"><?php echo e(__('Flag')); ?></th>
                            <th class="pb-3"><?php echo e(__('Name')); ?></th>
                            <th class="pb-3"><?php echo e(__('Native Name')); ?></th>
                            <th class="pb-3"><?php echo e(__('Code')); ?></th>
                            <th class="pb-3"><?php echo e(__('Direction')); ?></th>
                            <th class="pb-3"><?php echo e(__('Status')); ?></th>
                            <th class="pb-3"><?php echo e(__('Default')); ?></th>
                            <th class="pb-3 text-right"><?php echo e(__('Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr>
                            <td class="py-4 text-lg"><?php echo e($language->flag ?? '--'); ?></td>
                            <td class="py-4">
                                <span class="font-semibold text-ink"><?php echo e($language->name); ?></span>
                            </td>
                            <td class="py-4 text-sm text-ink"><?php echo e($language->native_name ?? '--'); ?></td>
                            <td class="py-4">
                                <span class="text-xs font-mono text-muted bg-surface px-2 py-0.5 rounded"><?php echo e($language->code); ?></span>
                            </td>
                            <td class="py-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($language->direction === 'rtl'): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-full">RTL</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-full">LTR</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="py-4">
                                <form method="POST" action="<?php echo e(route('admin.languages.toggle', $language)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="inline-flex items-center gap-1.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($language->is_active): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-success/15 text-success rounded-full"><?php echo e(__('Active')); ?></span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-surface text-ink/80 rounded-full"><?php echo e(__('Inactive')); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </button>
                                </form>
                            </td>
                            <td class="py-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($language->is_default): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-brand/15 text-brand rounded-full"><?php echo e(__('Default')); ?></span>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo e(route('admin.languages.default', $language)); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="text-xs text-muted hover:text-brand transition"><?php echo e(__('Set Default')); ?></button>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" class="btn-secondary" x-data @click="$dispatch('open-modal', 'edit-language-<?php echo e($language->id); ?>')"><?php echo e(__('Edit')); ?></button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($language->is_default)): ?>
                                    <button type="button" class="btn-danger" x-data @click="$dispatch('open-modal', 'delete-language-<?php echo e($language->id); ?>')"><?php echo e(__('Delete')); ?></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        
                        <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'edit-language-'.e($language->id).'','maxWidth' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'edit-language-'.e($language->id).'','maxWidth' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <form method="POST" action="<?php echo e(route('admin.languages.update', $language)); ?>" class="p-6 space-y-5">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <div>
                                    <h3 class="text-lg font-semibold text-ink"><?php echo e(__('Edit Language')); ?></h3>
                                    <p class="mt-1 text-sm text-muted">Update the details for "<?php echo e($language->name); ?>".</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="edit_name_<?php echo e($language->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Name')); ?> <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="edit_name_<?php echo e($language->id); ?>" value="<?php echo e($language->name); ?>" required class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_code_<?php echo e($language->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Code')); ?> <span class="text-danger">*</span></label>
                                        <input type="text" name="code" id="edit_code_<?php echo e($language->id); ?>" value="<?php echo e($language->code); ?>" required maxlength="10" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_native_<?php echo e($language->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Native Name')); ?></label>
                                        <input type="text" name="native_name" id="edit_native_<?php echo e($language->id); ?>" value="<?php echo e($language->native_name); ?>" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_flag_<?php echo e($language->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Flag Emoji')); ?></label>
                                        <input type="text" name="flag" id="edit_flag_<?php echo e($language->id); ?>" value="<?php echo e($language->flag); ?>" maxlength="10" class="input-field">
                                    </div>
                                    <div>
                                        <label for="edit_direction_<?php echo e($language->id); ?>" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Direction')); ?> <span class="text-danger">*</span></label>
                                        <select name="direction" id="edit_direction_<?php echo e($language->id); ?>" class="input-field">
                                            <option value="ltr" <?php if($language->direction === 'ltr'): echo 'selected'; endif; ?>><?php echo e(__('LTR (Left to Right)')); ?></option>
                                            <option value="rtl" <?php if($language->direction === 'rtl'): echo 'selected'; endif; ?>><?php echo e(__('RTL (Right to Left)')); ?></option>
                                        </select>
                                    </div>
                                    <div class="flex items-end gap-6">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" value="1" <?php if($language->is_active): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-border bg-surface-2/80 text-brand focus:ring-brand/40">
                                            <span class="text-sm text-ink"><?php echo e(__('Active')); ?></span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="hidden" name="is_default" value="0">
                                            <input type="checkbox" name="is_default" value="1" <?php if($language->is_default): echo 'checked'; endif; ?> class="h-4 w-4 rounded border-border bg-surface-2/80 text-brand focus:ring-brand/40">
                                            <span class="text-sm text-ink"><?php echo e(__('Default')); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                                    <button type="submit" class="btn-primary"><?php echo e(__('Update Language')); ?></button>
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

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($language->is_default)): ?>
                        <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'delete-language-'.e($language->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'delete-language-'.e($language->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <form method="POST" action="<?php echo e(route('admin.languages.destroy', $language)); ?>" class="p-6 space-y-4">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <div>
                                    <p class="text-lg font-semibold text-ink">Delete "<?php echo e($language->name); ?>"?</p>
                                    <p class="mt-2 text-sm text-muted"><?php echo __('This language will be <strong class="text-danger">permanently removed</strong>. This action cannot be undone.'); ?></p>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                                    <button type="submit" class="btn-danger"><?php echo e(__('Delete Language')); ?></button>
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
                            <td colspan="8" class="py-6 text-center text-sm text-muted"><?php echo e(__('No languages found.')); ?></td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($languages->hasPages()): ?>
            <div class="mt-4 border-t border-border/60 pt-4">
                <?php echo e($languages->withQueryString()->links()); ?>

            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'create-language','maxWidth' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'create-language','maxWidth' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <form method="POST" action="<?php echo e(route('admin.languages.store')); ?>" class="p-6 space-y-5">
            <?php echo csrf_field(); ?>
            <div>
                <h3 class="text-lg font-semibold text-ink"><?php echo e(__('Add Language')); ?></h3>
                <p class="mt-1 text-sm text-muted"><?php echo e(__('Add a new language to your application.')); ?></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="create_name" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Name')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="create_name" required class="input-field" placeholder="<?php echo e(__('English')); ?>">
                </div>
                <div>
                    <label for="create_code" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Code')); ?> <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="create_code" required maxlength="10" class="input-field" placeholder="en">
                </div>
                <div>
                    <label for="create_native" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Native Name')); ?></label>
                    <input type="text" name="native_name" id="create_native" class="input-field" placeholder="<?php echo e(__('English')); ?>">
                </div>
                <div>
                    <label for="create_flag" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Flag Emoji')); ?></label>
                    <input type="text" name="flag" id="create_flag" maxlength="10" class="input-field" placeholder="🇺🇸">
                </div>
                <div>
                    <label for="create_direction" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Direction')); ?> <span class="text-danger">*</span></label>
                    <select name="direction" id="create_direction" class="input-field">
                        <option value="ltr"><?php echo e(__('LTR (Left to Right)')); ?></option>
                        <option value="rtl"><?php echo e(__('RTL (Right to Left)')); ?></option>
                    </select>
                </div>
                <div class="flex items-end gap-6">
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
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                <button type="submit" class="btn-primary"><?php echo e(__('Create Language')); ?></button>
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
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/languages/index.blade.php ENDPATH**/ ?>