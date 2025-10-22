@extends('gp247-core::layout')

@section('main')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header with-border">
                <h3 class="card-title">{{ gp247_language_render('Plugins/MFA::lang.users_management') }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin_mfa.index') }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> {{ gp247_language_render('Plugins/MFA::lang.back_to_dashboard') }}
                    </a>
                </div>
            </div>

            <div class="card-body">
                <p class="text-muted">{{ gp247_language_render('Plugins/MFA::lang.users_management_desc') }}</p>

                @if(count($enabledGuards) > 0)
                <!-- Guard Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>{{ gp247_language_render('Plugins/MFA::lang.select_guard') }}</label>
                        <select id="guardFilter" class="form-control">
                            @foreach($enabledGuards as $guard)
                            <option value="{{ $guard }}" {{ $currentGuard == $guard ? 'selected' : '' }}>
                                {{ gp247_language_render('Plugins/MFA::lang.guard_' . $guard) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($errorMsg)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> {{ $errorMsg }}
                </div>
                @else
                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ gp247_language_render('Plugins/MFA::lang.name') }}</th>
                                <th>{{ gp247_language_render('Plugins/MFA::lang.email') }}</th>
                                <th>{{ gp247_language_render('Plugins/MFA::lang.mfa_status') }}</th>
                                <th>{{ gp247_language_render('Plugins/MFA::lang.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($users->count() > 0)
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->display_name ?? '-' }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->two_factor_auth && $user->two_factor_auth->enabled)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> {{ gp247_language_render('Plugins/MFA::lang.enabled') }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-times"></i> {{ gp247_language_render('Plugins/MFA::lang.disabled') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->two_factor_auth && $user->two_factor_auth->enabled)
                                            <button class="btn btn-sm btn-danger reset-mfa-btn" 
                                                    data-id="{{ $user->id }}" 
                                                    data-name="{{ $user->display_name ?? '' }}" 
                                                    data-email="{{ $user->email }}">
                                                <i class="fas fa-trash"></i> {{ gp247_language_render('Plugins/MFA::lang.reset_mfa') }}
                                            </button>
                                        @else
                                            <span class="text-muted">{{ gp247_language_render('Plugins/MFA::lang.no_mfa') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">{{ gp247_language_render('Plugins/MFA::lang.no_users_found') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="row">
                    <div class="col-sm-12">
                        {{ $users->appends(['guard' => $currentGuard])->links() }}
                    </div>
                </div>
                @endif

                @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> {{ gp247_language_render('Plugins/MFA::lang.no_enabled_guards') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reset MFA Confirmation Modal -->
<div class="modal fade" id="resetMfaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ gp247_language_render('Plugins/MFA::lang.reset_mfa') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ gp247_language_render('Plugins/MFA::lang.reset_mfa_confirm') }}</p>
                <p><strong>{{ gp247_language_render('Plugins/MFA::lang.user') }}:</strong> <span id="resetUserName"></span></p>
                <p><strong>{{ gp247_language_render('Plugins/MFA::lang.email') }}:</strong> <span id="resetUserEmail"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    {{ gp247_language_render('Plugins/MFA::lang.cancel') }}
                </button>
                <button type="button" class="btn btn-danger" id="confirmResetMfa">
                    <i class="fas fa-trash"></i> {{ gp247_language_render('Plugins/MFA::lang.reset_mfa') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let resetUserId = null;
    let currentGuard = '{{ $currentGuard }}';

    // Change guard and reload page with pjax
    $('#guardFilter').on('change', function() {
        const guard = $(this).val();
        if (guard) {
            window.location.href = '{{ url(GP247_ADMIN_PREFIX . "/mfa/users") }}/' + guard;
        }
    });

    // Show reset confirmation modal
    $(document).on('click', '.reset-mfa-btn', function() {
        resetUserId = $(this).data('id');
        $('#resetUserName').text($(this).data('name') || '-');
        $('#resetUserEmail').text($(this).data('email'));
        $('#resetMfaModal').modal('show');
    });

    // Confirm reset MFA
    $('#confirmResetMfa').on('click', function() {
        if (!resetUserId || !currentGuard) return;
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ gp247_language_render('Plugins/MFA::lang.processing') }}');
        
        $.ajax({
            url: '{{ url(GP247_ADMIN_PREFIX . "/mfa/reset-user") }}/' + currentGuard,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                user_id: resetUserId
            },
            success: function(response) {
                if (response.error === 0) {
                    alertJs('success', response.msg);
                    $('#resetMfaModal').modal('hide');
                    setTimeout(function() {
                        location.reload(); // Reload page to update data
                    }, 1000);
                } else {
                    alertJs('error', response.msg || 'An error occurred');
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred';
                try {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.msg) {
                        errorMsg = xhr.responseJSON.msg;
                    } else if (xhr.responseText) {
                        errorMsg = xhr.responseText;
                    }
                } catch (e) {
                    errorMsg = 'Error: ' + xhr.status;
                }
                alertJs('error', errorMsg);
            },
            complete: function() {
                $('#confirmResetMfa').prop('disabled', false).html('<i class="fas fa-trash"></i> {{ gp247_language_render('Plugins/MFA::lang.reset_mfa') }}');
            }
        });
    });
});
</script>
@endpush
