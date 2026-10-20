<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('Friends Settings'),'subtitle' => __('Friend chat, file sharing and audio calls.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Friends Settings')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Friend chat, file sharing and audio calls.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6 max-w-3xl">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="rounded-xl border border-green-300 bg-green-50 text-green-800 px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="rounded-xl border border-red-300 bg-red-50 text-red-800 px-4 py-3 text-sm"><?php echo e($errors->first()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.friends-settings.save')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>
            <div class="panel p-6 space-y-4">
                <h3 class="text-base font-semibold text-ink"><?php echo e(__('Chat, files and audio calls')); ?></h3>
                <p class="text-sm text-muted"><?php echo e(__('Applies to the website and the mobile app. Only accepted friends can chat and call each other.')); ?></p>
                <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="friends_chat_enabled" value="1" <?php if($chat): echo 'checked'; endif; ?>><span class="text-sm font-medium text-ink"><?php echo e(__('Friend chat')); ?></span></label>
                <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="friends_files_enabled" value="1" <?php if($files): echo 'checked'; endif; ?>><span class="text-sm font-medium text-ink"><?php echo e(__('Sending and receiving files')); ?></span></label>
                <div class="ml-7 flex flex-wrap items-center gap-2 text-sm text-muted"><?php echo e(__('Largest file')); ?>

                    <input type="number" name="friends_file_max_mb" min="1" max="50" value="<?php echo e(old('friends_file_max_mb', $maxMb)); ?>" class="input w-24"> MB
                    <span class="text-xs"><?php echo e(__('(programs such as .exe, .apk, .sh and web pages are never accepted)')); ?></span>
                </div>
                <label class="flex items-center gap-3 cursor-pointer"><input type="checkbox" name="friends_calls_enabled" value="1" <?php if($calls): echo 'checked'; endif; ?>><span class="text-sm font-medium text-ink"><?php echo e(__('Audio calls')); ?></span></label>
            </div>

            
            <div class="panel p-6 space-y-4">
                <h3 class="text-base font-semibold text-ink"><?php echo e(__('Call & meeting recording')); ?></h3>
                <p class="text-xs text-muted"><?php echo e(__('Choose ONE policy for all audio calls, video calls and meetings. When a recording ends, the file is posted into the chat as a normal media message (a voice message for audio, a video file for video). In meetings, a link appears in the meeting chat and every registered participant gets a notification. Guests without an account cannot receive files.')); ?></p>

                <div class="rounded-xl border border-amber-300 bg-amber-50 text-amber-900 text-xs p-3">
                    <strong><?php echo e(__('Always visible, by design:')); ?></strong> <?php echo e(__('while anything is being recorded, every participant sees a REC icon or a banner (at least one of the two stays on). Recording people with no visible sign is illegal in many countries and is rejected by the App Store and Google Play, so these switches can hide buttons and sounds, never the recording sign itself. Mention recording in your privacy policy and terms.')); ?>

                </div>

                <div class="space-y-2">
                    <label class="flex items-start gap-3 cursor-pointer rounded-xl border border-border p-3"><input type="radio" name="rec_mode" value="0" class="mt-1" <?php if($recMode === 0): echo 'checked'; endif; ?>><span><span class="text-sm font-semibold text-ink"><?php echo e(__('Off')); ?></span><span class="block text-xs text-muted"><?php echo e(__('Nothing can be recorded.')); ?></span></span></label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Services\Recording\RecordingPolicy::MODES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n => $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <label class="flex items-start gap-3 cursor-pointer rounded-xl border border-border p-3"><input type="radio" name="rec_mode" value="<?php echo e($n); ?>" class="mt-1" <?php if($recMode === $n): echo 'checked'; endif; ?>><span><span class="text-sm font-semibold text-ink"><?php echo e(__('Mode :n', ['n' => $n])); ?> · <?php echo e(__($m['name'])); ?></span><span class="block text-xs text-muted"><?php echo e(__($m['text'])); ?></span></span></label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <h4 class="text-sm font-semibold text-ink pt-2"><?php echo e(__('What people see and hear, per mode')); ?></h4>
                <p class="text-xs text-muted"><?php echo e(__('Only the settings of the mode you selected above are used. You can prepare the others.')); ?></p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-left text-xs text-muted"><th class="py-2 pr-3"><?php echo e(__('Mode')); ?></th><th class="py-2 pr-3"><?php echo e(__('Record button visible to')); ?></th><th class="py-2 pr-3"><?php echo e(__('REC icon')); ?></th><th class="py-2 pr-3"><?php echo e(__('Banner "This call is being recorded"')); ?></th><th class="py-2"><?php echo e(__('Chime / voice prompt on start & stop')); ?></th></tr></thead>
                        <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Services\Recording\RecordingPolicy::MODES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n => $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <?php ($c = $recCfg[$n]); ?>
                            <tr class="border-t border-border">
                                <td class="py-2 pr-3 font-medium text-ink whitespace-nowrap"><?php echo e($n); ?> · <?php echo e(__($m['name'])); ?></td>
                                <td class="py-2 pr-3">
                                    <select name="rec[<?php echo e($n); ?>][button]" class="input">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Services\Recording\RecordingPolicy::ALLOWED_BUTTONS[$n]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?><option value="<?php echo e($b); ?>" <?php if($c['button'] === $b): echo 'selected'; endif; ?>><?php echo e(__(\App\Services\Recording\RecordingPolicy::BUTTONS[$b])); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </td>
                                <td class="py-2 pr-3"><input type="checkbox" name="rec[<?php echo e($n); ?>][icon]" value="1" <?php if($c['icon']): echo 'checked'; endif; ?>></td>
                                <td class="py-2 pr-3"><input type="checkbox" name="rec[<?php echo e($n); ?>][banner]" value="1" <?php if($c['banner']): echo 'checked'; endif; ?>></td>
                                <td class="py-2"><input type="checkbox" name="rec[<?php echo e($n); ?>][chime]" value="1" <?php if($c['chime']): echo 'checked'; endif; ?>></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-muted"><?php echo e(__('If both the icon and the banner are switched off, the banner is kept on. Mode 1 has no record button (it starts by itself); in modes 4 only the host or the initiator can have the button.')); ?></p>

                <div class="flex flex-wrap items-center gap-4 text-sm text-muted">
                    <label class="flex items-center gap-2"><?php echo e(__('Consent request expires after')); ?> <input type="number" name="rec_consent_seconds" min="10" max="120" value="<?php echo e(old('rec_consent_seconds', $recSeconds)); ?>" class="input w-20"> <?php echo e(__('seconds')); ?></label>
                    <label class="flex items-center gap-2"><?php echo e(__('Largest recording')); ?> <input type="number" name="rec_max_mb" min="5" max="500" value="<?php echo e(old('rec_max_mb', $recMb)); ?>" class="input w-24"> MB</label>
                </div>
                <p class="text-xs text-muted"><?php echo e(__('Today the audio / video is captured by a device that can do it – the website in a browser. Phone apps show the button, the consent question and the REC sign, but cannot capture yet; if nobody in the call can capture, the person who started the recording is told. Raise upload_max_filesize and post_max_size in PHP to match the size above.')); ?></p>
            </div>

            <div class="panel p-6 space-y-3">
                <h3 class="text-base font-semibold text-ink"><?php echo e(__('Call servers')); ?></h3>
                <p class="text-xs text-muted"><?php echo e(__('Calls connect the two devices directly. A STUN server helps them find each other; a TURN server relays the audio when a direct connection is impossible (strict networks, many mobile carriers). Without TURN some calls will not connect.')); ?></p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2"><label class="block text-xs text-muted mb-1"><?php echo e(__('STUN server')); ?></label><input type="text" name="friends_stun_url" value="<?php echo e(old('friends_stun_url', $stun)); ?>" class="input w-full"></div>
                    <div class="sm:col-span-2"><label class="block text-xs text-muted mb-1"><?php echo e(__('TURN server address(es), comma separated (optional)')); ?></label><input type="text" name="friends_turn_url" value="<?php echo e(old('friends_turn_url', $turnUrl)); ?>" class="input w-full" placeholder="turn:turn.your-domain.com:3478"></div>
                    <div><label class="block text-xs text-muted mb-1"><?php echo e(__('TURN username')); ?></label><input type="text" name="friends_turn_user" value="<?php echo e(old('friends_turn_user', $turnUser)); ?>" class="input w-full" autocomplete="off"></div>
                    <div><label class="block text-xs text-muted mb-1"><?php echo e(__('TURN password')); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($turnPassSet): ?><span class="text-green-700">(<?php echo e(__('saved')); ?>)</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label><input type="password" name="friends_turn_pass" class="input w-full" autocomplete="new-password" placeholder="<?php echo e($turnPassSet ? __('leave empty to keep') : ''); ?>"></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($turnPassSet): ?><label class="sm:col-span-2 flex items-center gap-2 text-xs text-muted"><input type="checkbox" name="friends_turn_pass_clear" value="1"> <?php echo e(__('Remove the saved TURN password')); ?></label><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <button type="submit" class="btn-primary"><?php echo e(__('Save')); ?></button>
        </form>
    </div>
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
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/friends-settings.blade.php ENDPATH**/ ?>