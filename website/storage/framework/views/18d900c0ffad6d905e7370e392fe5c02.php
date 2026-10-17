<?php if (isset($component)) { $__componentOriginal1e6834b7596effc838ab3adb1475b477 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1e6834b7596effc838ab3adb1475b477 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.guest','data' => ['title' => __('Save your recovery phrase')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.guest'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Save your recovery phrase'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div>
        <h2 class="text-2xl font-bold text-ink"><?php echo e(__('Your recovery phrase')); ?></h2>
        <p class="mt-1 text-sm text-muted">
            <?php echo e(__('Write these 12 words down in order and keep them somewhere safe and offline. We will not show them again. If you ever forget your password, this phrase is the only way back into your account.')); ?>

        </p>
    </div>

    <ol class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-2" aria-label="<?php echo e(__('Recovery phrase')); ?>" id="recovery-words">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $words; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $word): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <li class="flex items-center gap-2 rounded-lg border border-border bg-surface-2 px-3 py-2 text-sm">
                <span class="text-muted tabular-nums"><?php echo e($i + 1); ?>.</span>
                <span class="font-medium text-ink"><?php echo e($word); ?></span>
            </li>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </ol>

    <div class="mt-4 flex gap-3">
        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('recovery-words').innerText.replace(/\n+/g,' ').trim())"
                class="flex-1 py-2 px-3 bg-surface-2 border border-border rounded-xl text-sm font-medium text-ink hover:bg-surface transition-colors">
            <?php echo e(__('Copy')); ?>

        </button>
        <button type="button" onclick="window.print()"
                class="flex-1 py-2 px-3 bg-surface-2 border border-border rounded-xl text-sm font-medium text-ink hover:bg-surface transition-colors">
            <?php echo e(__('Print')); ?>

        </button>
    </div>

    <form method="POST" action="<?php echo e(route('recovery-phrase.confirm')); ?>" class="mt-6 space-y-4">
        <?php echo csrf_field(); ?>
        <div class="flex items-start">
            <input id="saved" type="checkbox" name="saved" value="1" required
                   class="w-4 h-4 mt-0.5 rounded border-border text-brand focus:ring-brand">
            <label for="saved" class="ml-2 text-sm text-muted"><?php echo e(__("I've saved my recovery phrase somewhere safe.")); ?></label>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['saved'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-danger"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <button type="submit" class="btn-primary w-full py-2.5">
            <?php echo e(__('Continue')); ?>

        </button>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1e6834b7596effc838ab3adb1475b477)): ?>
<?php $attributes = $__attributesOriginal1e6834b7596effc838ab3adb1475b477; ?>
<?php unset($__attributesOriginal1e6834b7596effc838ab3adb1475b477); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1e6834b7596effc838ab3adb1475b477)): ?>
<?php $component = $__componentOriginal1e6834b7596effc838ab3adb1475b477; ?>
<?php unset($__componentOriginal1e6834b7596effc838ab3adb1475b477); ?>
<?php endif; ?>
<?php /**PATH /home/dahimail.com/public_html/resources/views/auth/recovery-phrase.blade.php ENDPATH**/ ?>