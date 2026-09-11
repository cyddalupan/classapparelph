@extends('layouts.app')

@section('page-title', 'My Profile')

@section('content')
<style>
    .pf-wrap { max-width: 900px; }
    .pf-card {
        background: #fff;
        border: 1px solid #eef0f4;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(17, 24, 39, .05);
        margin-bottom: 18px;
        overflow: hidden;
    }
    .pf-card-head {
        padding: 14px 20px;
        border-bottom: 1px solid #f1f3f7;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .pf-card-head i { color: #2563eb; font-size: 15px; }
    .pf-card-head h5 { margin: 0; font-size: 15px; font-weight: 700; color: #111827; }
    .pf-card-head .sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
    .pf-card-body { padding: 20px; }
    .pf-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #6b7280; margin-bottom: 4px; }
    .pf-value { font-size: 14px; color: #111827; font-weight: 600; padding: 8px 0; border-bottom: 1px dashed #eef0f4; }
    .pf-value:last-child { border-bottom: 0; }
    .pf-avatar-lg {
        width: 96px; height: 96px; border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem; font-weight: 700; overflow: hidden; flex: 0 0 auto;
        box-shadow: 0 6px 18px rgba(37, 99, 235, .28);
    }
    .pf-avatar-lg img { width: 100%; height: 100%; object-fit: cover; }
    .pf-input {
        width: 100%; padding: 9px 12px; border: 1px solid #d7dbe3; border-radius: 9px;
        font-size: 14px; outline: none; transition: border-color .15s;
    }
    .pf-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .pf-btn {
        display: inline-flex; align-items: center; gap: 7px;
        background: #2563eb; color: #fff; border: 0; border-radius: 9px;
        padding: 9px 18px; font-size: 13.5px; font-weight: 600; cursor: pointer;
    }
    .pf-btn:hover { background: #1d4ed8; }
    .pf-btn.secondary { background: #f1f5f9; color: #334155; }
    .pf-btn.secondary:hover { background: #e2e8f0; }
    .pf-alert { border-radius: 9px; padding: 10px 14px; font-size: 13px; margin-bottom: 14px; }
    .pf-alert.ok { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .pf-alert.err { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .pf-hint { font-size: 12px; color: #6b7280; margin-top: 6px; }
    .pf-badge-role {
        display: inline-block; background: #eff6ff; color: #1d4ed8;
        font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
        text-transform: capitalize;
    }
</style>

<div class="pf-wrap">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 style="margin:0;font-weight:800;color:#111827;">My Profile</h4>
            <div class="pf-hint">I-manage ang iyong account details, username, at password.</div>
        </div>
    </div>

    @if (session('status') === 'username-updated')
        <div class="pf-alert ok"><i class="fas fa-check-circle"></i> Na-update na ang username mo.</div>
    @endif
    @if (session('status') === 'avatar-updated')
        <div class="pf-alert ok"><i class="fas fa-check-circle"></i> Na-update na ang profile picture mo.</div>
    @endif
    @if (session('status') === 'password-updated')
        <div class="pf-alert ok"><i class="fas fa-check-circle"></i> Na-update na ang password mo.</div>
    @endif

    {{-- Profile Information (view-only) --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <i class="fas fa-id-card"></i>
            <div>
                <h5>Profile Information</h5>
                <div class="sub">Ito ang impormasyon ng iyong account. Hindi ito na-eedit dito.</div>
            </div>
        </div>
        <div class="pf-card-body">
            <div class="d-flex align-items-center gap-4 mb-4">
                <div class="pf-avatar-lg">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div style="font-size:20px;font-weight:800;color:#111827;">{{ $user->name }}</div>
                    @if($user->position)
                        <div style="font-size:13px;font-weight:700;color:#7c3aed;margin:2px 0;">{{ $user->position }}</div>
                    @endif
                    <span class="pf-badge-role">{{ str_replace('_', ' ', $user->role) }}</span>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="pf-label">Full Name</div>
                    <div class="pf-value">{{ $user->name }}</div>
                </div>
                <div class="col-md-6">
                    <div class="pf-label">Username</div>
                    <div class="pf-value">{{ $user->username ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="pf-label">Email</div>
                    <div class="pf-value">{{ $user->email }}</div>
                </div>
                <div class="col-md-6">
                    <div class="pf-label">Position / Title</div>
                    <div class="pf-value">{{ $user->position ?: '—' }}</div>
                </div>
                @if($user->phone)
                <div class="col-md-6">
                    <div class="pf-label">Phone</div>
                    <div class="pf-value">{{ $user->phone }}</div>
                </div>
                @endif
                @if($user->company_name)
                <div class="col-md-6">
                    <div class="pf-label">Company</div>
                    <div class="pf-value">{{ $user->company_name }}</div>
                </div>
                @endif
                @if($user->address)
                <div class="col-md-12">
                    <div class="pf-label">Address</div>
                    <div class="pf-value">{{ $user->address }}</div>
                </div>
                @endif
                <div class="col-md-6">
                    <div class="pf-label">Member Since</div>
                    <div class="pf-value">{{ optional($user->created_at)->format('M d, Y') ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Profile Picture --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <i class="fas fa-camera"></i>
            <div>
                <h5>Profile Picture</h5>
                <div class="sub">Ito ang ginagamit sa dalawang maliit na picture (sidebar + upper-right menu).</div>
            </div>
        </div>
        <div class="pf-card-body">
            <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="d-flex align-items-center gap-3 flex-wrap">
                @csrf
                <div class="pf-avatar-lg" style="width:72px;height:72px;font-size:1.6rem;">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-grow-1" style="min-width:240px;">
                    <input id="avatar" name="avatar" type="file" accept="image/*" class="pf-input" required>
                    <div class="pf-hint">JPG, PNG, GIF o WEBP. Max 2MB.</div>
                    @if($errors->get('avatar'))
                        <div class="pf-alert err mt-2">{{ $errors->first('avatar') }}</div>
                    @endif
                </div>
                <button type="submit" class="pf-btn"><i class="fas fa-upload"></i> Upload Picture</button>
            </form>
        </div>
    </div>

    {{-- Change Username --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <i class="fas fa-at"></i>
            <div>
                <h5>Change Username</h5>
                <div class="sub">Letters, numbers, dot, underscore at hyphen lang (3–30 characters).</div>
            </div>
        </div>
        <div class="pf-card-body">
            <form method="POST" action="{{ route('profile.username.update') }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="pf-label">New Username</div>
                        <input id="username" name="username" type="text" class="pf-input"
                               value="{{ old('username', $user->username) }}"
                               placeholder="hal. andrew.ceo" required>
                        @if($errors->updateUsername->get('username'))
                            <div class="pf-alert err mt-2">{{ $errors->updateUsername->first('username') }}</div>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="pf-btn"><i class="fas fa-save"></i> Save Username</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <i class="fas fa-lock"></i>
            <div>
                <h5>Change Password</h5>
                <div class="sub">Kailangan ng current password para makapagpalit.</div>
            </div>
        </div>
        <div class="pf-card-body">
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4">
                        <div class="pf-label">Current Password</div>
                        <input id="current_password" name="current_password" type="password" class="pf-input" autocomplete="current-password" required>
                        @if($errors->updatePassword->get('current_password'))
                            <div class="pf-alert err mt-2">{{ $errors->updatePassword->first('current_password') }}</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="pf-label">New Password</div>
                        <input id="password" name="password" type="password" class="pf-input" autocomplete="new-password" required>
                        @if($errors->updatePassword->get('password'))
                            <div class="pf-alert err mt-2">{{ $errors->updatePassword->first('password') }}</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="pf-label">Confirm New Password</div>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="pf-input" autocomplete="new-password" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="pf-btn"><i class="fas fa-key"></i> Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
