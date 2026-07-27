<div>
    <x-header title="نقش‌ها و مجوزها" separator progress-indicator>
        <x-slot:actions>
            <x-button label="نقش جدید" icon="o-plus" class="btn-primary btn-soft" wire:click="$set('showCreateModal', true)" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table :headers="[['key' => 'title', 'label' => 'عنوان نقش'], ['key' => 'permissions_count', 'label' => 'تعداد مجوز']]" :rows="$roles" :sort-by="$sortBy">
            @scope('actions', $role)
                <div class="flex gap-1">
                    @can('manage permissions')
                        <x-button icon="o-key" wire:click="editPermissions({{ $role['id'] }})" spinner class="btn-ghost btn-sm"
                            tooltip="مجوزها" />
                    @endcan
                    @can('manage roles')
                        <x-button icon="o-trash" wire:click="deleteRole({{ $role['id'] }})" wire:confirm="نقش حذف بشه؟"
                            spinner class="btn-ghost btn-sm text-error" tooltip="حذف" />
                    @endcan
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- مودال ساخت نقش --}}
    <x-modal wire:model="showCreateModal" title="ساخت نقش جدید">
        <x-form wire:submit="createRole">
            <x-input label="نام نقش" wire:model="newRoleName" placeholder="مثلاً: author" />
            <x-input label="عنوان نقش" wire:model="newRoleTitle" placeholder="مثلاً: نویسنده" />
            <x-slot:actions>
                <x-button label="انصراف" class="btn-error btn-soft" wire:click="$set('showCreateModal', false)" />
                <x-button label="ذخیره" type="submit" class="btn-success btn-soft" spinner="createRole" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- مودال ویرایش مجوزها --}}
    <x-modal wire:model="showEditModal" title="ویرایش مجوزها">
        <div class="grid grid-cols-2 gap-2">
            @foreach ($permissions as $permission)
                <x-checkbox label="{{ $permission['title'] ?? $permission['name'] }}" value="{{ $permission['name'] }}"
                    wire:model="checkedPermissions" />
            @endforeach
        </div>
        <x-slot:actions>
            <x-button label="انصراف" class="btn-error btn-soft" wire:click="$set('showEditModal', false)" />
            <x-button label="ذخیره" wire:click="savePermissions" class="btn-success btn-soft" spinner="savePermissions" />
        </x-slot:actions>
    </x-modal>
</div>
