

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name' => 'file', 'accept' => '.csv,.txt,.xlsx', 'label' => 'CSV File', 'required' => true]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['name' => 'file', 'accept' => '.csv,.txt,.xlsx', 'label' => 'CSV File', 'required' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div>
    <label class="panel-heading"><?php echo e($label); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($required): ?><span class="text-danger">*</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label>
    <div
        x-data="{ isDragging: false, fileName: '' }"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="isDragging = false; const f = $event.dataTransfer.files[0]; if(f) { $refs.fileInput.files = $event.dataTransfer.files; fileName = f.name; }"
        class="mt-2 flex cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed p-8 text-center transition-all"
        :class="isDragging ? 'border-brand bg-brand/5 text-brand' : 'border-border bg-surface text-muted hover:border-brand/40 hover:bg-brand/5'"
        @click="$refs.fileInput.click()"
    >
        <div class="flex h-12 w-12 items-center justify-center rounded-full border border-border bg-surface-2">
            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" x2="12" y1="3" y2="15"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium" x-show="!fileName">Browse file to upload</p>
            <p class="text-sm font-medium text-brand" x-show="fileName" x-text="fileName" x-cloak></p>
            <p class="mt-1 text-xs text-muted">or drag & drop</p>
        </div>
        <input
            type="file"
            name="<?php echo e($name); ?>"
            accept="<?php echo e($accept); ?>"
            <?php echo e($required ? 'required' : ''); ?>

            class="sr-only"
            x-ref="fileInput"
            @change="fileName = $el.files[0]?.name || ''"
        >
    </div>
</div>
<?php /**PATH /home/dahimail.com/public_html/resources/views/components/file-uploader.blade.php ENDPATH**/ ?>