<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ gp247_language_render('Plugins/MFA::lang.setup_mfa') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
        }
        .setup-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .qr-code-container {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
        }
        .secret-key {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 2px;
            border: 2px dashed #667eea;
            margin: 20px 0;
            word-break: break-all;
        }
        .step-indicator {
            display: flex;
            justify-content: space-around;
            padding: 20px 0;
            margin-bottom: 20px;
        }
        .step {
            text-align: center;
            flex: 1;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-card">
            <div class="card-header">
                <i class="fas fa-shield-alt fa-3x mb-3"></i>
                <h2>{{ gp247_language_render('Plugins/MFA::lang.setup_mfa') }}</h2>
                <p class="mb-0">{{ gp247_language_render('Plugins/MFA::lang.description') }}</p>
            </div>

            <div class="card-body p-4">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="step-indicator">
                    <div class="step active">
                        <div class="step-number">1</div>
                        <small>{{ gp247_language_render('Plugins/MFA::lang.scan_qr_code') }}</small>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <small>{{ gp247_language_render('Plugins/MFA::lang.verify_code') }}</small>
                    </div>
                </div>

                <!-- QR Code Section -->
                <div class="qr-code-container">
                    <h5 class="mb-3">{{ gp247_language_render('Plugins/MFA::lang.scan_qr_code') }}</h5>
                    <p class="text-muted">{{ gp247_language_render('Plugins/MFA::lang.scan_qr_code_desc') }}</p>
                    <div class="my-4">
                        {!! $qrCodeSvg !!}
                    </div>
                </div>

                <!-- Manual Entry Section -->
                <div class="text-center p-4">
                    <h6>{{ gp247_language_render('Plugins/MFA::lang.manual_entry') }}</h6>
                    <p class="text-muted small">{{ gp247_language_render('Plugins/MFA::lang.manual_entry_desc') }}</p>
                    <div class="secret-key">
                        {{ $secret }}
                    </div>
                </div>

                <!-- Verification Form -->
                <div class="p-4 border-top">
                    <h5 class="mb-3">{{ gp247_language_render('Plugins/MFA::lang.verify_code') }}</h5>
                    <p class="text-muted">{{ gp247_language_render('Plugins/MFA::lang.verify_code_desc') }}</p>
                    
                    <form action="{{ route('mfa.setup.enable') }}" method="POST">
                        @csrf
                        <input type="hidden" name="guard" value="{{ $guard }}">
                        
                        <div class="mb-3">
                            <input type="text" 
                                   name="code" 
                                   class="form-control form-control-lg text-center" 
                                   placeholder="{{ gp247_language_render('Plugins/MFA::lang.enter_code') }}"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   required
                                   autofocus>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> {{ gp247_language_render('Plugins/MFA::lang.enable_mfa') }}
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> {{ gp247_language_render('Plugins/MFA::lang.cancel') }}
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Help Section -->
                <div class="p-4 bg-light mt-3 rounded">
                    <h6><i class="fas fa-question-circle"></i> {{ gp247_language_render('Plugins/MFA::lang.help_title') }}</h6>
                    <small class="text-muted">
                        {{ gp247_language_render('Plugins/MFA::lang.help_supported_apps_desc') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

