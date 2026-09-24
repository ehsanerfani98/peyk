<div>
    <x-header title="مدیریت کاربران" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="جستجو..." wire:model.live.debounce.400ms="search" icon="o-magnifying-glass" clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="کاربر جدید" icon="o-plus" class="btn-primary btn-soft" wire:click="openCreateUser" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table :headers="[
            ['key' => 'name', 'label' => 'نام'],
            ['key' => 'email', 'label' => 'ایمیل'],
            ['key' => 'roles', 'label' => 'نقش‌ها', 'sortable' => false],
        ]" :rows="$users" :sort-by="$sortBy" with-pagination>
            @scope('cell_roles', $user)
                <div class="flex gap-1 flex-wrap">
                    @foreach ($user->roles as $role)
                        <x-badge :value="$role->title ?? $role->name" class="badge-soft badge-sm" />
                    @endforeach
                </div>
            @endscope

            @scope('actions', $user)
                <div class="flex gap-1">
                    @can('manage users')
                        <x-button icon="o-pencil" wire:click="editUserInfo({{ $user->id }})" class="btn-ghost btn-sm"
                            tooltip="ویرایش اطلاعات" />
                        <x-button icon="o-shield-check" wire:click="editRoles({{ $user->id }})" class="btn-ghost btn-sm"
                            tooltip="نقش‌ها" />
                        <x-button icon="o-key" wire:click="openResetPassword({{ $user->id }})" class="btn-ghost btn-sm"
                            tooltip="ریست رمز عبور" />
                        <x-button icon="o-trash" wire:click="deleteUser({{ $user->id }})" wire:confirm="کاربر حذف بشه؟"
                            spinner class="btn-ghost btn-sm text-error" tooltip="حذف" />
                    @endcan
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="showEditModal" title="ویرایش نقش‌های کاربر">
        <div class="flex flex-col gap-2">
            @foreach ($roles as $title => $role)
                <x-checkbox label="{{ $title }}" value="{{ $role }}" wire:model="checkedRoles" />
            @endforeach
        </div>
        <x-slot:actions>
            <x-button label="انصراف" class="btn-error btn-soft" wire:click="$set('showEditModal', false)" />
            <x-button label="ذخیره" wire:click="saveRoles" class="btn-success btn-soft" spinner="saveRoles" />
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model="showUserEditModal" title="ویرایش اطلاعات کاربر">
        <x-form wire:submit="saveUserInfo">
            <x-input label="نام" wire:model="editName" icon="o-user" />
            <x-input label="شماره موبایل" wire:model="editMobile" icon="o-phone" placeholder="09xxxxxxxxx" />
            <x-input label="ایمیل" wire:model="editEmail" icon="o-envelope" hint="برای مشتری‌های موبایل‌محور می‌تواند خالی بماند" />
            <x-slot:actions>
                <x-button label="انصراف" class="btn-error btn-soft" wire:click="$set('showUserEditModal', false)" />
                <x-button label="ذخیره" type="submit" class="btn-success btn-soft" spinner="saveUserInfo" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-modal wire:model="showPasswordModal" title="تنظیم رمز عبور جدید">
        <x-form wire:submit="resetPassword">
            <x-input label="رمز عبور جدید" wire:model="newPassword" type="password" icon="o-key" />
            <x-input label="تکرار رمز عبور" wire:model="newPassword_confirmation" type="password" icon="o-key" />
            <x-slot:actions>
                <x-button label="انصراف" class="btn-error btn-soft" wire:click="$set('showPasswordModal', false)" />
                <x-button label="ذخیره" type="submit" class="btn-success btn-soft" spinner="resetPassword" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-modal wire:model="showCreateUserModal" title="ساخت کاربر جدید">
        <x-form wire:submit="createUser">
            <x-input label="نام" wire:model="createName" icon="o-user" />
            <x-input label="شماره موبایل" wire:model="createMobile" icon="o-phone" placeholder="09xxxxxxxxx" />
            <x-input label="ایمیل" wire:model="createEmail" icon="o-envelope" hint="برای مشتری‌های موبایل‌محور می‌تواند خالی بماند" />
            <x-input label="رمز عبور" wire:model="createPassword" type="password" icon="o-key" />
            <x-input label="تکرار رمز عبور" wire:model="createPassword_confirmation" type="password" icon="o-key" />

            <div class="mt-2">
                <label class="label-text mb-1 block">نقش‌ها</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($roles as $title => $role)
                        <x-checkbox label="{{ $title }}" value="{{ $role }}" wire:model="createRoles" />
                    @endforeach
                </div>
            </div>

            <x-slot:actions>
                <x-button label="انصراف" class="btn-error btn-soft" wire:click="$set('showCreateUserModal', false)" />
                <x-button label="ذخیره" type="submit" class="btn-success btn-soft" spinner="createUser" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
