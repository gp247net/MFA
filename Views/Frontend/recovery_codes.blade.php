<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ gp247_language_render('Plugins/MFA::lang.recovery_codes') }}</title>
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
        .recovery-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
        }
        .recovery-card {
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
        .recovery-codes-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding: 20px;
        }
        .recovery-code {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .recovery-code:hover {
            background: #e9ecef;
            border-color: #667eea;
            transform: translateY(-2px);
        }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px;
        }
        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none !important;
            }
            .recovery-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card">
            <div class="card-header no-print">
                <i class="fas fa-key fa-3x mb-3"></i>
                <h2>{{ gp247_language_render('Plugins/MFA::lang.recovery_codes') }}</h2>
                <p class="mb-0">{{ gp247_language_render('Plugins/MFA::lang.recovery_codes_desc') }}</p>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success no-print">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning no-print">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                    </div>
                @endif

                <div class="warning-box">
                    <h5 class="text-warning mb-3">
                        <i class="fas fa-exclamation-triangle"></i> {{ gp247_language_render('Plugins/MFA::lang.help_title') }}
                    </h5>
                    <p class="mb-0">{{ gp247_language_render('Plugins/MFA::lang.recovery_codes_warning') }}</p>
                </div>

                @if($recoveryCodes && count($recoveryCodes) > 0)
                    <div class="recovery-codes-grid" id="recovery-codes-grid">
                        @foreach($recoveryCodes as $code)
                            <div class="recovery-code" onclick="copyCode('{{ $code }}')">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>

                    <div class="p-4 no-print">
                        <div class="d-grid gap-2">
                            <button onclick="window.print()" class="btn btn-outline-primary">
                                <i class="fas fa-print"></i> {{ gp247_language_render('Plugins/MFA::lang.print_recovery_codes') }}
                            </button>
                            <button onclick="downloadCodes()" class="btn btn-outline-secondary">
                                <i class="fas fa-download"></i> {{ gp247_language_render('Plugins/MFA::lang.download_recovery_codes') }}
                            </button>
                            <a href="{{ route($guardConfig['redirect_after_verify']) }}" class="btn btn-primary">
                                <i class="fas fa-check"></i> {{ gp247_language_render('Plugins/MFA::lang.continue') }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="p-4 text-center">
                        <p class="text-muted">{{ gp247_language_render('Plugins/MFA::lang.mfa_not_enabled') }}</p>
                        <a href="{{ route('mfa.setup.show', $guard) }}" class="btn btn-primary">
                            {{ gp247_language_render('Plugins/MFA::lang.setup_mfa') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyCode(code) {
            navigator.clipboard.writeText(code).then(function() {
                // Show temporary feedback
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = '✓ Copied!';
                btn.style.background = '#d4edda';
                btn.style.borderColor = '#28a745';
                
                setTimeout(function() {
                    btn.textContent = originalText;
                    btn.style.background = '';
                    btn.style.borderColor = '';
                }, 1000);
            });
        }

        function downloadCodes() {
            const codes = @json($recoveryCodes ?? []);
            const content = 'Recovery Codes for {{ config("Plugins/MFA.app_name") }}\n' +
                          'Generated: ' + new Date().toLocaleString() + '\n' +
                          'Guard: {{ $guard }}\n\n' +
                          'IMPORTANT: Keep these codes in a safe place!\n' +
                          'Each code can only be used once.\n\n' +
                          codes.join('\n');
            
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'recovery-codes-{{ $guard }}.txt';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }
    </script>
</body>
</html>

