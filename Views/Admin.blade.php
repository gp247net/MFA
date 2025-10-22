@extends('gp247-core::layout')

@section('main')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header with-border">
                <h3 class="card-title">{{ gp247_language_render('Plugins/MFA::lang.admin_title') }}</h3>
            </div>

            <div class="card-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="mfaTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="dashboard-tab" href="#dashboard">
                            <i class="fas fa-chart-bar"></i> {{ gp247_language_render('Plugins/MFA::lang.dashboard') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="users-tab" href="{{ route('admin_mfa.users') }}">
                            <i class="fas fa-users"></i> {{ gp247_language_render('Plugins/MFA::lang.users_management') }}
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="mfaTabContent">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4>{{ gp247_language_render('Plugins/MFA::lang.statistics') }}</h4>
                    </div>
                    @foreach($guards as $guard)
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-shield-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ gp247_language_render('Plugins/MFA::lang.guard_' . $guard) }}</span>
                                <span class="info-box-number">
                                    {{ $stats[$guard]['mfa_enabled'] ?? 0 }} / {{ $stats[$guard]['total_users'] ?? 0 }}
                                </span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: {{ $stats[$guard]['percentage'] ?? 0 }}%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ gp247_language_render('Plugins/MFA::lang.adoption_rate') }}: {{ $stats[$guard]['percentage'] ?? 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <hr>

                <!-- Settings Display (Read-Only) -->
                <div class="row">
                    <div class="col-12">
                        <h4>{{ gp247_language_render('Plugins/MFA::lang.guard_settings') }}</h4>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            {{ gp247_language_render('Plugins/MFA::lang.config_readonly_note') }}
                            <br>
                            <strong>{{ gp247_language_render('Plugins/MFA::lang.config_file_path') }}:</strong> 
                            <code>app/GP247/Plugins/MFA/config.php</code>
                        </div>
                    </div>
                </div>

                    @foreach($guards as $guard)
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>{{ gp247_language_render('Plugins/MFA::lang.guard_' . $guard) }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ gp247_language_render('Plugins/MFA::lang.enabled') }}</label>
                                        <div class="icheck-primary">
                                            <input type="checkbox" 
                                                   id="enabled_{{ $loop->index }}"
                                                   {{ $settings[$guard]['enabled'] ? 'checked' : '' }}
                                                   disabled>
                                            <label for="enabled_{{ $loop->index }}"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ gp247_language_render('Plugins/MFA::lang.forced') }}</label>
                                        <div class="icheck-primary">
                                            <input type="checkbox" 
                                                   id="forced_{{ $loop->index }}"
                                                   {{ $settings[$guard]['forced'] ? 'checked' : '' }}
                                                   disabled>
                                            <label for="forced_{{ $loop->index }}"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ gp247_language_render('Plugins/MFA::lang.qr_code_size') }}</label>
                                        <input type="number" 
                                               id="qr_code_size_{{ $loop->index }}"
                                               value="{{ $settings[$guard]['qr_code_size'] }}"
                                               class="form-control"
                                               readonly>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ gp247_language_render('Plugins/MFA::lang.recovery_codes_count') }}</label>
                                        <input type="number" 
                                               id="recovery_codes_count_{{ $loop->index }}"
                                               value="{{ $settings[$guard]['recovery_codes_count'] }}"
                                               class="form-control"
                                               readonly>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ gp247_language_render('Plugins/MFA::lang.window') }}</label>
                                        <select id="window_{{ $loop->index }}" class="form-control" disabled>
                                            <option value="0" {{ $settings[$guard]['window'] == 0 ? 'selected' : '' }}>0</option>
                                            <option value="1" {{ $settings[$guard]['window'] == 1 ? 'selected' : '' }}>1</option>
                                            <option value="2" {{ $settings[$guard]['window'] == 2 ? 'selected' : '' }}>2</option>
                                            <option value="3" {{ $settings[$guard]['window'] == 3 ? 'selected' : '' }}>3</option>
                                            <option value="4" {{ $settings[$guard]['window'] == 4 ? 'selected' : '' }}>4</option>
                                            <option value="5" {{ $settings[$guard]['window'] == 5 ? 'selected' : '' }}>5</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    </div>
                    <!-- End Dashboard Tab -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .info-box {
        display: block;
        min-height: 90px;
        background: #fff;
        width: 100%;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        border-radius: 2px;
        margin-bottom: 15px;
    }
    .info-box-icon {
        border-top-left-radius: 2px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 2px;
        display: block;
        float: left;
        height: 90px;
        width: 90px;
        text-align: center;
        font-size: 45px;
        line-height: 90px;
        background: rgba(0,0,0,0.2);
    }
    .info-box-content {
        padding: 5px 10px;
        margin-left: 90px;
    }
</style>
@endpush

