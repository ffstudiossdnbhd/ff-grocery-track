<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Utama') | FFGroceryTrack</title>
    
    <!-- Meta PWA -->
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="FFGrocery">
    
    <!-- PWA Icons -->
    <link rel="apple-touch-icon" href="/images/icon-192.png">
    <link rel="manifest" href="/manifest.json">
    
    <!-- CSS Utama -->
    <link rel="stylesheet" href="/css/app.css">
    
    <!-- FontAwesome (untuk ikon sampingan) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Header Telefon Bimbit -->
    <div class="mobile-header">
        <div class="logo-container" style="margin-bottom: 0;">
            <div class="logo-icon">F</div>
            <div class="logo-text" style="font-size: 1.25rem;">FFGrocery</div>
        </div>
        <button id="sidebarToggle" class="btn btn-secondary btn-sm" style="padding: 8px 12px;">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <div class="app-container">
        
        <!-- Sidebar Utama -->
        <aside id="appSidebar" class="sidebar">
            <div class="logo-container">
                <div class="logo-icon">F</div>
                <span class="logo-text">FFGrocery</span>
            </div>
            
            <nav class="nav-menu">
                <a href="{{ route('inventori.index') }}" class="nav-item {{ Request::routeIs('inventori.index') || Request::is('/') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Inventori</span>
                </a>
                
                <a href="{{ route('inventori.restok') }}" class="nav-item {{ Request::routeIs('inventori.restok') ? 'active' : '' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Perlu Restok</span>
                </a>
                
                <a href="{{ route('tuntutan.index') }}" class="nav-item {{ Request::routeIs('tuntutan.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Tuntutan</span>
                </a>
                
                @role('Superadmin')
                <a href="{{ route('pengguna.index') }}" class="nav-item {{ Request::routeIs('pengguna.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i>
                    <span>Pengurusan Pengguna</span>
                </a>
                
                <a href="{{ route('log_aktiviti.index') }}" class="nav-item {{ Request::routeIs('log_aktiviti.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Log Aktiviti</span>
                </a>
                @endrole
            </nav>
            
            <!-- Profil Pengguna & Log Keluar -->
            @auth
            <div class="user-profile-section">
                <div class="profile-info">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="profile-details">
                        <div class="profile-name">{{ Auth::user()->name }}</div>
                        <div class="profile-role">
                            {{ Auth::user()->roles->first()?->name ?? 'Tiada Peranan' }}
                        </div>
                    </div>
                </div>
                
                <form action="{{ route('logout') }}" method="POST" style="margin-top: 8px;">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Log Keluar</span>
                    </button>
                </form>
            </div>
            @endauth
        </aside>
        
        <!-- Kandungan Utama -->
        <main class="main-content">
            <!-- Mesej Notifikasi Sukses / Gagal -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
                </div>
            @endif
            
            @yield('content')
        </main>
        
    </div>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker didaftarkan berjaya:', reg.scope))
                    .catch(err => console.error('Pendaftaran Service Worker gagal:', err));
            });
        }
        
        // Pengurusan menu togol telefon bimbit
        const sidebarToggle = document.getElementById('sidebarToggle');
        const appSidebar = document.getElementById('appSidebar');
        
        if (sidebarToggle && appSidebar) {
            sidebarToggle.addEventListener('click', () => {
                appSidebar.classList.toggle('open');
            });
            
            // Klik luar menu untuk tutup sidebar di telefon bimbit
            document.addEventListener('click', (e) => {
                if (!appSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    appSidebar.classList.remove('open');
                }
            });
        }
    </script>
</body>
</html>
