@extends('vendor_portal.layout')

@section('title', $title ?? 'Portal Erişimi')

@section('content')
<div style="max-width: 560px; margin: 60px auto; text-align: center;">
    <div class="portal-card" style="padding: 40px 30px; border-radius: 16px;">
        <div style="width: 68px; height: 68px; background: #fff1f2; border: 2px solid #fecdd3; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 30px;">
            🔒
        </div>
        
        <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 12px;">
            {{ $title }}
        </h2>

        @if(isset($vendor))
            <div style="display: inline-block; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 14px; margin-bottom: 16px; font-size: 13px; font-weight: 700; color: #334155;">
                🏢 {{ $vendor->company_name }}
            </div>
        @endif

        <p style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 24px;">
            {{ $message }}
        </p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; font-size: 12px; color: #64748b; text-align: left;">
            <strong>Bilgilendirme:</strong> Herhangi bir sorunuz varsa veya yeni bir belge iletmeniz gerekiyorsa lütfen Köksan ilgili departman yetkiliniz veya Proje Yöneticisi ile iletişime geçiniz.
        </div>
    </div>
</div>
@endsection
