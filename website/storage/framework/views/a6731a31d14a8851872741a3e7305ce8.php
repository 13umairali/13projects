<?php if (isset($component)) { $__componentOriginala9d35bca55314290701d0fd9db1a187a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala9d35bca55314290701d0fd9db1a187a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.settings','data' => ['title' => __('Profile Settings')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.settings'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Profile Settings'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    
    <div x-data="{ loaded: false }" x-init="
        const observer = new MutationObserver((mutations) => {
            for (const m of mutations) {
                for (const node of m.addedNodes) {
                    if (node.nodeType === 1 && node.hasAttribute('wire:id')) {
                        loaded = true;
                        observer.disconnect();
                        return;
                    }
                }
            }
        });
        observer.observe($el, { childList: true, subtree: true });
        setTimeout(() => { loaded = true; observer.disconnect(); }, 5000);
    ">
        
        <div x-show="!loaded" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="space-y-8">
            
            <div class="bg-surface-2 rounded-xl border border-border dark:border-gray-700 p-6 animate-pulse">
                <div class="h-5 w-32 bg-gray-200 dark:bg-gray-700 rounded mb-6"></div>
                <div class="flex items-center gap-6">
                    <div class="h-20 w-20 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
                    <div class="space-y-2">
                        <div class="h-9 w-32 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                        <div class="h-3 w-48 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    </div>
                </div>
            </div>

            
            <div class="bg-surface-2 rounded-xl border border-border dark:border-gray-700 p-6 animate-pulse">
                <div class="h-5 w-44 bg-gray-200 dark:bg-gray-700 rounded mb-6"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 4; $i++): ?>
                    <div class="space-y-2">
                        <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-10 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    </div>
                    <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="space-y-2 mt-6">
                    <div class="h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="h-24 w-full bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                </div>
            </div>

            
            <div class="bg-surface-2 rounded-xl border border-border dark:border-gray-700 p-6 animate-pulse">
                <div class="h-5 w-40 bg-gray-200 dark:bg-gray-700 rounded mb-6"></div>
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 3; $i++): ?>
                    <div class="flex items-center justify-between py-2">
                        <div class="space-y-1.5">
                            <div class="h-4 w-36 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            <div class="h-3 w-56 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </div>
                        <div class="h-6 w-11 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    </div>
                    <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="flex justify-end">
                <div class="h-10 w-32 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
            </div>
        </div>

        
        <div x-show="loaded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('settings.profile-form', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2437974973-0', $__key);

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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala9d35bca55314290701d0fd9db1a187a)): ?>
<?php $attributes = $__attributesOriginala9d35bca55314290701d0fd9db1a187a; ?>
<?php unset($__attributesOriginala9d35bca55314290701d0fd9db1a187a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala9d35bca55314290701d0fd9db1a187a)): ?>
<?php $component = $__componentOriginala9d35bca55314290701d0fd9db1a187a; ?>
<?php unset($__componentOriginala9d35bca55314290701d0fd9db1a187a); ?>
<?php endif; ?>
<?php /**PATH /home/dahimail.com/public_html/resources/views/settings/profile.blade.php ENDPATH**/ ?>