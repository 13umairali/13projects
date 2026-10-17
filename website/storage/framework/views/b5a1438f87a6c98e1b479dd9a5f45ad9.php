<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => __('Knowledge Base')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Knowledge Base'))]); ?>
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
        
        <div x-show="!loaded" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="space-y-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <div class="h-8 w-44 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                    <div class="h-4 w-72 bg-gray-200 dark:bg-gray-700 rounded mt-2 animate-pulse"></div>
                </div>
                <div class="flex gap-3">
                    <div class="h-10 w-32 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                    <div class="h-10 w-36 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                </div>
            </div>

            
            <div class="flex items-center gap-3">
                <div class="h-10 flex-1 max-w-md bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                <div class="h-10 w-28 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 3; $i++): ?>
                <div class="bg-surface-2  rounded-xl border border-border dark:border-gray-700 p-5 animate-pulse">
                    <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                    <div class="h-7 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 9; $i++): ?>
                <div class="bg-surface-2  rounded-xl border border-border dark:border-gray-700 p-5 animate-pulse">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-lg flex-shrink-0"></div>
                        <div class="flex-1 space-y-1.5">
                            <div class="h-4 w-3/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            <div class="h-3 w-full bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="h-5 w-16 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                        <div class="h-3 w-20 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    </div>
                </div>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div x-show="loaded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('knowledge-base.document-manager', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2353914353-0', $__key);

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
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH /home/dahimail.com/public_html/resources/views/knowledge-base/index.blade.php ENDPATH**/ ?>