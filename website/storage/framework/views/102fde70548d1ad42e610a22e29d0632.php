
<?php
    use App\Models\SystemSetting;
    $isEnabled = SystemSetting::enabled($enabled_key ?? '', false);
?>

<div x-data="{ open: <?php echo e($isEnabled ? 'true' : 'false'); ?> }">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-ink uppercase tracking-wider"><?php echo e($title); ?></h3>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="<?php echo e($enabled_key); ?>" value="false">
            <input type="checkbox" name="<?php echo e($enabled_key); ?>" value="true" class="sr-only peer" x-model="open" <?php echo e($isEnabled ? 'checked' : ''); ?>>
            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:ring-2 peer-focus:ring-brand/20 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm peer-checked:bg-brand"></div>
        </label>
    </div>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1">
        <div class="grid grid-cols-1 sm:grid-cols-<?php echo e(min(count($fields), 3)); ?> gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <div>
                <label class="settings-label"><?php echo e($field['label']); ?></label>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($field['type'] ?? 'text') === 'password'): ?>
                    <?php $hasValue = !empty(SystemSetting::get($field['name'])); ?>
                    <input type="password"
                           name="<?php echo e($field['name']); ?>"
                           value=""
                           placeholder="<?php echo e($hasValue ? '••••••••••••' : ($field['placeholder'] ?? '')); ?>"
                           class="settings-input font-mono text-xs">
                <?php else: ?>
                    <input type="<?php echo e($field['type'] ?? 'text'); ?>"
                           name="<?php echo e($field['name']); ?>"
                           value="<?php echo e(SystemSetting::get($field['name'])); ?>"
                           placeholder="<?php echo e($field['placeholder'] ?? ''); ?>"
                           class="settings-input font-mono text-xs">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($help)): ?>
        <p class="text-xs text-muted mt-2"><?php echo $help; ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/settings/_integration.blade.php ENDPATH**/ ?>