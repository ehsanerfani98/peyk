<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] #[Title('مدیریت کاربران')] class extends Component
{
    use Toast;
    use WithPagination;

    #[Url]
    public string $search = '';

    public array $roles = [];

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public bool $showEditModal = false;

    public ?int $editingUserId = null;

    public array $checkedRoles = [];

    // ویرایش اطلاعات کاربر
    public bool $showUserEditModal = false;

    public ?int $editingUserInfoId = null;

    public ?string $editName = '';

    public ?string $editEmail = '';

    public ?string $editingUserMobile = null;

    // ریست رمز عبور
    public bool $showPasswordModal = false;

    public ?int $resettingPasswordUserId = null;

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

    // ساخت کاربر جدید
    public bool $showCreateUserModal = false;

    public string $createName = '';

    public string $createEmail = '';

    public string $createPassword = '';

    public string $createPassword_confirmation = '';

    public array $createRoles = [];

    public function mount(): void
    {
        $this->roles = Role::pluck('name', 'title')->toArray();
    }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                ->with('roles')
                ->orderBy(...array_values($this->sortBy))
                ->paginate(10),
        ];
    }

    public function editRoles(int $userId): void
    {
        $user = User::findOrFail($userId);

        // ادمین نتونه نقش خودش رو دستکاری کنه (جلوگیری از قفل شدن دسترسی)
        if ($user->id === auth()->id()) {
            $this->warning(
                'نمی‌تونی نقش خودت رو ویرایش کنی',
                position: 'toast-bottom toast-end'
            );

            return;
        }

        $this->editingUserId = $user->id;
        $this->checkedRoles = $user->roles->pluck('name')->toArray();
        $this->showEditModal = true;
    }

    public function saveRoles(): void
    {
        abort_unless(auth()->user()->can('manage users'), 403);

        $this->validate([
            'checkedRoles' => 'required|array|min:1',
        ], [], ['checkedRoles' => 'نقش‌ها']);

        $user = User::findOrFail($this->editingUserId);
        $user->syncRoles($this->checkedRoles);

        $this->showEditModal = false;
        $this->success(
            'نقش‌های کاربر به‌روزرسانی شد',
            position: 'toast-bottom toast-end'
        );
    }

    public function editUserInfo(int $userId): void
    {
        // برای ویرایش اطلاعات خودش باید از صفحه پروفایل استفاده کنه
        if ($userId === auth()->id()) {
            $this->info(
                'برای ویرایش اطلاعات خودت به صفحه پروفایل برو',
                position: 'toast-bottom toast-end'
            );

            return;
        }

        $user = User::findOrFail($userId);

        $this->editingUserInfoId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editingUserMobile = $user->mobile;
        $this->showUserEditModal = true;
    }

    public function saveUserInfo(): void
    {
        abort_unless(auth()->user()->can('manage users'), 403);

        $this->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => 'nullable|email|unique:users,email,'.$this->editingUserInfoId,
        ], [], ['editName' => 'نام', 'editEmail' => 'ایمیل']);

        User::findOrFail($this->editingUserInfoId)->update([
            'name' => $this->editName,
            'email' => filled($this->editEmail) ? $this->editEmail : null,
        ]);

        $this->showUserEditModal = false;
        $this->success(
            'اطلاعات کاربر به‌روزرسانی شد',
            position: 'toast-bottom toast-end'
        );
    }

    public function openResetPassword(int $userId): void
    {
        $this->resettingPasswordUserId = $userId;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->showPasswordModal = true;
    }

    public function resetPassword(): void
    {
        abort_unless(auth()->user()->can('manage users'), 403);

        $this->validate([
            'newPassword' => 'required|confirmed|min:8',
        ], [], ['newPassword' => 'رمز عبور جدید']);

        User::findOrFail($this->resettingPasswordUserId)->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->showPasswordModal = false;
        $this->success(
            'رمز عبور کاربر تغییر کرد',
            position: 'toast-bottom toast-end'
        );
    }

    public function deleteUser(int $userId): void
    {
        abort_unless(auth()->user()->can('manage users'), 403);

        if ($userId === auth()->id()) {
            $this->error(
                'نمی‌تونی خودت رو حذف کنی',
                position: 'toast-bottom toast-end'
            );

            return;
        }

        User::findOrFail($userId)->delete();
        $this->success(
            'کاربر حذف شد',
            position: 'toast-bottom toast-end'
        );
    }

    public function openCreateUser(): void
    {
        $this->reset('createName', 'createEmail', 'createPassword', 'createPassword_confirmation', 'createRoles');
        $this->showCreateUserModal = true;
    }

    public function createUser(): void
    {
        abort_unless(auth()->user()->can('manage users'), 403);

        $this->validate([
            'createName' => 'required|string|max:255',
            'createEmail' => 'required|email|unique:users,email',
            'createPassword' => 'required|confirmed|min:8',
            'createRoles' => 'required|array|min:1',
        ], [], [
            'createName' => 'نام',
            'createEmail' => 'ایمیل',
            'createPassword' => 'رمز عبور',
            'createRoles' => 'نقش‌ها',
        ]);

        $user = User::create([
            'name' => $this->createName,
            'email' => $this->createEmail,
            'password' => Hash::make($this->createPassword),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles($this->createRoles);

        $this->showCreateUserModal = false;
        $this->success(
            'کاربر جدید ساخته شد',
            position: 'toast-bottom toast-end'
        );
    }
};
