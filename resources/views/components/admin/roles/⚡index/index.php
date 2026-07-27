<?php

use App\Models\Role;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Permission;

new #[Layout('layouts.app')] #[Title('نقش‌ها و مجوزها')] class extends Component
{
    use Toast;

    public array $roles = [];

    public array $permissions = [];

    // مودال ساخت نقش جدید
    public bool $showCreateModal = false;

    public string $newRoleName = '';

    public string $newRoleTitle = '';

    // مودال ویرایش مجوزهای یک نقش
    public bool $showEditModal = false;

    public ?int $editingRoleId = null;

    public array $checkedPermissions = [];

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->roles = Role::withCount('permissions')->orderBy(...array_values($this->sortBy))->get()->toArray();
        $this->permissions = Permission::all()->toArray();
    }

    public function updatedSortBy(): void
    {
        $this->loadData();
    }

    public function createRole(): void
    {
        $this->validate([
            'newRoleName' => 'required|string|max:255|unique:roles,name',
            'newRoleTitle' => 'string|max:255|unique:roles,title',
        ], [], ['newRoleName' => 'نام نقش', 'newRoleTitle' => 'عنوان نقش']);

        Role::create(['name' => $this->newRoleName, 'title' => $this->newRoleTitle, 'guard_name' => 'web']);

        $this->newRoleName = '';
        $this->newRoleTitle = '';
        $this->showCreateModal = false;
        $this->loadData();

        $this->success(
            'نقش جدید ساخته شد',
            position: 'toast-bottom toast-end'
        );

    }

    public function editPermissions(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->checkedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showEditModal = true;
    }

    public function savePermissions(): void
    {
        $role = Role::findOrFail($this->editingRoleId);
        $role->syncPermissions($this->checkedPermissions);

        $this->showEditModal = false;
        $this->loadData();
        $this->success(
            'مجوزها به‌روزرسانی شد',
            position: 'toast-bottom toast-end'
        );
    }

    public function deleteRole(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        if ($role->name === 'admin') {
            $this->error(
                'نقش مدیر قابل حذف نیست',
                position: 'toast-bottom toast-end'
            );

            return;
        }

        $role->delete();
        $this->loadData();
        $this->success(
            'نقش با موفقیت حذف شد',
            position: 'toast-bottom toast-end'
        );

    }
};
