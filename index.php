<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();
$db = getDB();

// Ambil data kategori lomba
$kategori_lomba = $db->fetchAll("SELECT * FROM b_kategori_lomba WHERE status = 'aktif' ORDER BY nama");

// Ambil data webinar aktif
$webinars = $db->fetchAll("SELECT * FROM b_webinar WHERE status = 'aktif' ORDER BY tanggal, judul");

// Jika user sudah login, ambil data user
if ($auth->isLoggedIn()) {
    $currentUser = $auth->getCurrentUser();
    $userPendaftaran = $auth->getUserPendaftaran();
}

// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bluvocation Creative Fest - Lomba Kreatif untuk SD/SMP/SMA/Sederajat</title>
    <link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css" rel="stylesheet">

    <style>
        html {
            scroll-behavior: smooth;
        }

        .hero-section {
            background: url('images/lomba.jpg') no-repeat center center;
            background-size: cover;
            /* biar selalu penuh */
            min-height: 100vh;
            /* tinggi full layar */
            position: relative;
            overflow: hidden;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            left: 70%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            left: 40%;
            animation-delay: 4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .navbar {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.95) 100%) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
        }

        /* Brand */
        .navbar-brand {
            font-weight: 800;
            color: #667eea !important;
            font-size: 1.6rem;
            text-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-brand:hover {
            color: #1630df !important;
            transform: scale(1.05);
        }

        .navbar-brand i {
            font-size: 1.9rem;
            background: linear-gradient(45deg, #667eea, #1630df);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Nav Links */
        .nav-link {
            color: #495057 !important;
            font-weight: 600;
            font-size: 1.05rem;
            padding: 10px 18px !important;
            margin: 0 4px;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #667eea !important;
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }

        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: linear-gradient(45deg, #667eea, #1630df);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .nav-link:hover::before {
            width: 70%;
        }

        /* Toggler */
        .navbar-toggler {
            border: none;
            padding: 8px 12px;
            border-radius: 10px;
            background: rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Responsive Breakpoints */
        @media (max-width: 880px) {
            .navbar {
                padding: 12px 0;
            }

            .navbar-brand {
                font-size: 1.4rem;
            }

            .navbar-brand i {
                font-size: 1.6rem;
            }

            .nav-link {
                font-size: 1rem;
                margin: 4px 0;
                display: block;
                text-align: center;
            }

            .nav-link:hover::before {
                width: 40%;
            }
        }

        @media (max-width: 576px) {
            .navbar {
                padding: 10px 0;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }

            .navbar-brand i {
                font-size: 1.4rem;
            }

            .nav-link {
                font-size: 0.95rem;
                padding: 8px 14px !important;
            }
        }

        .dropdown-menu {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            margin-top: 10px;
            box-sizing: content-box;
        }

        .dropdown-item {
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 10px;
            margin: 2px 8px;
        }

        .dropdown-item:hover {
            background: linear-gradient(45deg, #667eea, #1630df);
            color: white;
            transform: translateX(5px);
        }

        .btn-glow {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-glow:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .btn-outline-light {
            border: 2px solid white;
            color: white;
            background: transparent;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-outline-light:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .user-welcome {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        /* Avatar style */
        .user-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0046ff, #6a5cff);
            color: #fff;
            font-size: 28px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            transition: transform 0.3s ease;
        }

        .user-avatar:hover {
            transform: scale(1.1);
        }

        /* Glow button */
        .btn-glow {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: #fff;
            border: none;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 242, 254, 0.3);
            transition: all 0.3s ease;
        }

        .btn-glow:hover {
            box-shadow: 0 6px 18px rgba(0, 242, 254, 0.5);
            transform: translateY(-2px);
        }

        /* Background gradient */
        .bg-gradient {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .text-light-50 {
            color: rgba(255, 255, 255, 0.7);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 15px;
        }

        .stats-pending {
            background: linear-gradient(45deg, #ffc107, #ff9800);
        }

        .stats-approved {
            background: linear-gradient(45deg, #28a745, #20c997);
        }

        .stats-rejected {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
        }

        .pendaftaran-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .pendaftaran-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-daftar {
            background: linear-gradient(45deg, #667eea, #1630df);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-daftar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .pendaftaran-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }

        .pendaftaran-details h6 {
            color: #495057;
            margin-bottom: 10px;
        }

        .pendaftaran-details p {
            margin-bottom: 5px;
            color: #6c757d;
        }

        .lomba-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            transition: all 0.4s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .lomba-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(45deg, #667eea, #1630df);
        }

        .lomba-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .lomba-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            margin: 0 auto 20px;
            background: linear-gradient(45deg, #667eea, #1630df);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .lomba-image {
            text-align: center;
        }

        .lomba-image img {
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .lomba-image img:hover {
            transform: scale(1.05);
        }

        .lomba-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }

        .lomba-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
            text-align: center;
        }

        .lomba-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-detail {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .btn-daftar {
            background: linear-gradient(45deg, #667eea, #1630df);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-daftar:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .timeline {
            position: relative;
            padding: 40px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 80px;
            bottom: 0;
            width: 2px;
            background: linear-gradient(45deg, #667eea, #1630df);
            transform: translateX(-50%);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 60px;
            margin-top: 40px;
        }

        .timeline-item:nth-child(odd) {
            padding-right: 50%;
            padding-right: calc(50% + 20px);
        }

        .timeline-item:nth-child(even) {
            padding-left: 50%;
            padding-left: calc(50% + 20px);
        }

        .timeline-content {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .timeline-content::before {
            content: '';
            position: absolute;
            top: 20px;
            width: 0;
            height: 0;
            border: 10px solid transparent;
        }

        .timeline-item:nth-child(odd) .timeline-content::before {
            right: -20px;
            border-left-color: white;
        }

        .timeline-item:nth-child(even) .timeline-content::before {
            left: -20px;
            border-right-color: white;
        }

        .timeline-dot {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 20px;
            height: 20px;
            background: linear-gradient(45deg, #667eea, #1630df);
            border-radius: 50%;
            transform: translateX(-50%) translateY(-50%);
            z-index: 2;
            border: 3px solid white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
        }

        .prize-section {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            color: white;
            padding: 40px 0 80px 0;
        }

        .total-prize {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 40px;
        }

        .total-prize h1 {
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .text-white-75 {
            color: rgba(255, 255, 255, 0.75) !important;
        }

        .prize-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .prize-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.2);
        }

        .prize-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        /* Footer Styles */
        .footer-section {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 40px 0 20px;
            margin-top: 0;
        }

        .footer-title {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .footer-description {
            color: #bdc3c7;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #1630df);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .footer-subtitle {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .footer-links a:hover {
            color: #667eea;
            transform: translateX(5px);
        }

        .contact-info {
            margin-top: 10px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: #bdc3c7;
        }

        .contact-item i {
            color: #667eea;
            margin-right: 10px;
            width: 20px;
        }

        .footer-divider {
            border-color: #34495e;
            margin: 40px 0 20px;
        }

        .footer-bottom {
            padding-top: 20px;
        }

        .copyright-text {
            color: #95a5a6;
            margin: 0;
        }

        .footer-bottom-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
            justify-content: flex-end;
        }

        .footer-bottom-links a {
            color: #95a5a6;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-bottom-links a:hover {
            color: #667eea;
        }

        /* About Us Section Styling */
        #about_us p {
            font-size: 1.125rem;
            line-height: 1.8;
            color: #495057;
        }

        #about_us .lead {
            font-size: 1.125rem;
            color: #6c757d;
        }

        #about_us .fw-bold {
            font-size: 1.1rem;
            color: #334155;
        }

        #about_us small {
            font-size: 0.95rem;
            color: #6c757d;
        }

        #about_us .feature-list i {
            color: #667eea;
        }

        #about_us .feature-card {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
        }

        /* RESPONSIVE SETTINGS */

        /* Tablet */
        @media (max-width: 992px) {
            .hero-section {
                min-height: 70vh;
                padding: 60px 20px;
                text-align: center;
            }

            .hero-content {
                max-width: 100%;
            }

            .lomba-actions {
                flex-direction: column;
                gap: 12px;
            }

            .stats-card,
            .pendaftaran-card,
            .lomba-card {
                margin-bottom: 25px;
            }

            .timeline::before {
                left: 20px;
                /* pindah ke kiri di tablet */
            }

            .timeline-item {
                padding: 0 0 0 50px !important;
            }

            .timeline-item:nth-child(even),
            .timeline-item:nth-child(odd) {
                padding: 0 0 0 50px !important;
            }

            .timeline-content::before {
                left: -15px !important;
                right: auto !important;
                border-right-color: white !important;
                border-left-color: transparent !important;
            }

            .timeline-dot {
                left: 20px;
                transform: translateY(-50%);
            }
        }

        /* Mobile */
        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1rem;
            }

            .navbar-brand i {
                font-size: 1.4rem;
            }

            .nav-link {
                font-size: 0.95rem;
                padding: 8px 14px !important;
                display: block;
                text-align: center;
            }

            .hero-section {
                min-height: 60vh;
                padding: 40px 15px;
                text-align: center;
            }

            .btn-glow,
            .btn-outline-light,
            .btn-daftar,
            .btn-detail {
                width: 100%;
                text-align: center;
                padding: 12px;
            }

            .user-avatar {
                width: 60px;
                height: 60px;
                font-size: 22px;
            }

            .stats-card,
            .pendaftaran-card,
            .lomba-card,
            .prize-card {
                padding: 20px;
            }

            .lomba-title {
                font-size: 1.2rem;
            }

            .lomba-description {
                font-size: 0.95rem;
            }

            .timeline {
                padding: 20px 0;
            }

            .footer-section {
                text-align: center;
            }

            .social-links {
                justify-content: center;
            }

            .footer-bottom {
                text-align: center;
            }

            .footer-bottom-links {
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }
        }
    </style>
    <style>
        /* Enhanced modal styles */
        .modal-celebrate .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        }

        .modal-header-gradient {
            background: linear-gradient(135deg, #7c3aed, #4c1d95);
            color: #fff;
            border-bottom: 0;
            align-items: center;
        }

        .modal-header-gradient .modal-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .modal-header-gradient .title-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            animation: pop-in 280ms ease-out;
        }

        .modal-body-pretty {
            padding: 24px 24px 8px 24px;
            text-align: center;
        }

        .modal-body-pretty .big-icon {
            width: 74px;
            height: 74px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.12), rgba(76, 29, 149, 0.12));
            color: #7c3aed;
            font-size: 32px;
        }

        .modal-body-pretty h5 {
            font-weight: 800;
            color: #1f2937;
        }

        .modal-body-pretty p {
            color: #6b7280;
            margin: 0;
        }

        .modal-footer-soft {
            border-top: 0;
            padding-top: 0;
            padding-bottom: 20px;
        }

        .btn-purple {
            background: linear-gradient(135deg, #7c3aed, #4c1d95);
            color: #fff !important;
            border: none;
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.35);
        }

        .btn-purple:hover {
            filter: brightness(1.05);
            color: #fff !important;
        }

        @keyframes pop-in {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
    <style>
        /* Fancy modal styles */
        .modal-celebrate .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        }

        .modal-header-gradient {
            background: linear-gradient(135deg, #7c3aed, #4c1d95);
            color: #fff;
            border-bottom: 0;
            align-items: center;
        }

        .modal-header-gradient .modal-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .modal-header-gradient .title-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            animation: pop-in 300ms ease-out;
        }

        .modal-body-pretty {
            padding: 24px 24px 8px 24px;
            text-align: center;
        }

        .modal-body-pretty .big-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.15), rgba(76, 29, 149, 0.15));
            color: #7c3aed;
            font-size: 32px;
        }

        .modal-body-pretty h5 {
            font-weight: 700;
            color: #2b2d42;
        }

        .modal-body-pretty p {
            color: #6c757d;
            margin: 0;
        }

        .modal-footer-soft {
            border-top: 0;
            padding-top: 0;
            padding-bottom: 24px;
        }

        .btn-gradient-primary {
            background: linear-gradient(135deg, #667eea, #1630df);
            border: none;
        }

        .btn-gradient-primary:hover {
            filter: brightness(1.05);
        }

        @keyframes pop-in {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .confetti-piece {
            position: fixed;
            width: 8px;
            height: 12px;
            top: -10px;
            opacity: 0.9;
            transform: rotate(15deg);
            border-radius: 2px;
            z-index: 1056;
            /* above modal backdrop */
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let splide_gallery = new Splide("#gallery", {
                type: "loop",
                focus: "center",
                gap: '1rem',
                autoplay: true,
                pauseOnHover: true,
                arrows: true,
                pagination: true,
                perPage: 3,
                breakpoints: {
                    992: {
                        perPage: 2
                    },
                    576: {
                        perPage: 1
                    }
                }
            });
            splide_gallery.mount();
        })
    </script>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold py-0" href="index.php">
                <img src="images/bcf.png" alt="" width="50"> Bluvocation Creative Fest
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#kategori">Kategori Lomba</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#hadiah">Hadiah</a>
                    </li>
                    <?php if ($auth->isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#lomba-saya">Lomba Saya</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#webinar-saya">Webinar Saya</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($auth->isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($currentUser['nama_lengkap']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#lomba-saya">Lomba Saya</a></li>
                                <li><a class="dropdown-item" href="#webinar-saya">Webinar Saya</a></li>
                                <li><a class="dropdown-item" href="daftar.php">Daftar Lomba</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="index.php?logout=1">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <br />
    <br />
    <br />


    <section class="hero-section pt-10">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>

        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6 hero-content">
                    <h1 class="display-4 fw-bold text-white mb-4 animate__animated animate__fadeInUp">
                        Bluvocation Creative Fest
                    </h1>
                    <p class="lead text-white mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                        Platform lomba kreatif untuk siswa SD/SMP/SMA/SMK/Sederajat. Tunjukkan bakat dan kreativitasmu dalam berbagai kompetisi menarik!
                    </p>

                    <?php if ($auth->isLoggedIn()): ?>
                        <!-- User Welcome Section -->
                        <div class="user-welcome animate__animated animate__fadeInUp animate__delay-2s p-4 rounded shadow-sm bg-gradient">
                            <div class="row align-items-center">
                                <!-- Avatar -->
                                <div class="col-md-2 text-center">
                                    <div class="user-avatar mx-auto">
                                        <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                                    </div>
                                </div>
                                <!-- User Info -->
                                <div class="col-md-10">
                                    <h4 class="mb-1 fw-bold text-light">
                                        Halo, <?php echo htmlspecialchars($currentUser['username']); ?>
                                    </h4>
                                    <p class="mb-3 text-light-50">
                                        Selamat datang kembali di portal lomba!
                                    </p>
                                    <div class="d-flex gap-3">
                                        <a href="#lomba-saya" class="btn btn-glow flex-fill">
                                            <i class="fas fa-list"></i> Lomba Saya
                                        </a>
                                        <a href="#kategori" class="btn btn-outline-light flex-fill">
                                            <i class="fas fa-plus"></i> Daftar Baru
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Guest Section -->
                        <div class="text-center animate__animated animate__fadeInUp animate__delay-2s p-4 rounded shadow-sm bg-gradient">
                            <h5 class="text-light mb-4">Ayo gabung dan ikuti berbagai lomba seru!</h5>
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="register.php" class="btn btn-glow">
                                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                                </a>
                                <a href="login.php" class="btn btn-outline-light">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                                <a href="#kategori" class="btn btn-outline-light">
                                    <i class="fas fa-trophy"></i> Lihat Lomba
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6 text-center animate__animated animate__fadeInRight animate__delay-2s">
                    <!-- <i class="fas fa-trophy" style="font-size: 300px; color: rgba(255,255,255,0.3);"></i> -->
                    <img src="images/logo-bcf-bg.jpg" alt="" width="100%" style="border-radius: 25px;">
                </div>
            </div>
        </div>
    </section>

    <section id="about_us" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="text-center mb-2">
                    <i class="fas fa-info text-primary"></i> About Us
                </h2>
                <p class="lead mb-0">Merayakan kreativitas, kolaborasi, dan karya terbaik generasi muda.</p>
            </div>

            <div class="row align-items-center g-4">
                <div class="col-12 col-md-6 order-2 order-md-1">
                    <div class="text-center text-md-start">
                        <p>Kreativitas adalah kemampuan melahirkan gagasan, ide, atau produk baru yang berbeda dari sebelumnya. Melalui kreativitas, kita bisa menciptakan inovasi yang relevan dengan perkembangan zaman.</p>
                        <p>Di SMK Budi Luhur, program <strong>projek kreatif kewirausahaan</strong> mendorong siswa untuk merencanakan, memproduksi, dan memasarkan karya mereka�melatih keterampilan teknis, manajerial, serta kerja tim.</p>

                        <div class="row g-3 my-3 feature-list">
                            <div class="col-12 col-sm-6">
                                <div class="d-flex align-items-center p-3 feature-card">
                                    <i class="fas fa-film me-3"></i>
                                    <div>
                                        <div class="fw-bold mb-0">Lomba Kreatif</div>
                                        <small>Short video, infografis, robotik, dan lainnya</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="d-flex align-items-center p-3 feature-card">
                                    <i class="fas fa-users me-3"></i>
                                    <div>
                                        <div class="fw-bold mb-0">Terbuka Untuk Umum</div>
                                        <small>Kolaborasi karya lintas sekolah</small>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="col-12 col-md-6 order-1 order-md-2">
                    <div class="ratio ratio-16x9 rounded shadow-sm overflow-hidden">
                        <video src="videos/TEASER BLUE CREATIVE FESTIVAL.mp4" class="w-100 h-100" autoplay muted loop></video>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="gallery" class="py-5 splide" style="background: #f8f9fa;">
        <div class="splide__track">
            <ul class="splide__list">
                <li class="splide__slide p-3">
                    <img src="images/gallery/GQ1A1170.JPG" alt="Galeri 1" class="img-fluid rounded shadow-sm w-100 h-auto">
                </li>
                <li class="splide__slide p-3">
                    <img src="images/gallery/GQ1A1178.JPG" alt="Galeri 2" class="img-fluid rounded shadow-sm w-100 h-auto">
                </li>
                <li class="splide__slide p-3">
                    <img src="images/gallery/GQ1A1211.JPG" alt="Galeri 3" class="img-fluid rounded shadow-sm w-100 h-auto">
                </li>
                <li class="splide__slide p-3">
                    <img src="images/gallery/GQ1A1310.JPG" alt="Galeri 4" class="img-fluid rounded shadow-sm w-100 h-auto">
                </li>
                <li class="splide__slide p-3">
                    <img src="images/gallery/GQ1A1424.JPG" alt="Galeri 5" class="img-fluid rounded shadow-sm w-100 h-auto">
                </li>
                <li class="splide__slide p-3">
                    <img src="images/gallery/GQ1A1495.JPG" alt="Galeri 6" class="img-fluid rounded shadow-sm w-100 h-auto">
                </li>
                <li class="splide__slide p-3">
                    <img src="images/gallery/GQ1A1508.JPG" alt="Galeri 7" class="img-fluid rounded shadow-sm w-100 h-auto">
                </li>
                <li class="splide__slide p-3">
                    <img src="images/gallery/GQ1A1534.JPG" alt="Galeri 8" class="img-fluid rounded shadow-sm w-100 h-auto">
                </li>
            </ul>
        </div>
    </section>


    <!-- Kategori Lomba Section -->
    <section id="kategori" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">
                <i class="fas fa-trophy text-primary"></i> Kategori Lomba
            </h2>

            <div class="row">
                <?php foreach ($kategori_lomba as $lomba): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="lomba-card">
                            <?php if (!empty($lomba['image_path'])): ?>
                                <div class="lomba-image mb-3">
                                    <img src="<?= htmlspecialchars($lomba['image_path']) ?>"
                                        alt="<?= htmlspecialchars($lomba['nama']) ?>"
                                        class="img-fluid rounded shadow-sm"
                                        style="width: 100%; height: 200px; object-fit: cover;">
                                </div>
                            <?php else: ?>
                                <div class="lomba-icon">
                                    <i class="<?= htmlspecialchars($lomba['icon'] ?? 'fas fa-trophy') ?>"></i>
                                </div>
                            <?php endif; ?>
                            <h5 class="lomba-title"><?php echo htmlspecialchars($lomba['nama']); ?></h5>
                            <p class="lomba-description"><?php echo htmlspecialchars($lomba['deskripsi']); ?></p>
                            <div class="lomba-actions">
                                <a href="detail_lomba.php?id=<?php echo $lomba['id']; ?>" class="btn-detail">
                                    <i class="fas fa-info-circle"></i> Detail Lomba
                                </a>
                                <?php if ($auth->isLoggedIn()): ?>
                                    <a href="daftar.php?kategori=<?php echo $lomba['id']; ?>" class="btn-daftar btn-daftar-lomba" data-kategori="<?php echo $lomba['id']; ?>">
                                        <i class="fas fa-plus"></i> Daftar Sekarang
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" class="btn-daftar">
                                        <i class="fas fa-sign-in-alt"></i> Login untuk Daftar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Webinar Section -->
    <section id="webinar" class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <h2 class="text-center mb-5">
                <i class="fas fa-chalkboard-teacher text-primary"></i> Webinar
            </h2>
            <div class="row">
                <?php if (empty($webinars)): ?>
                    <div class="col-12 text-center text-muted">Belum ada webinar aktif.</div>
                <?php endif; ?>
                <?php foreach ($webinars as $ws): ?>
                    <?php
                    $kapasitas = (int)($ws['kapasitas'] ?? 0);
                    $countRow = $db->fetch('SELECT COUNT(*) AS cnt FROM b_webinar_pendaftar WHERE webinar_id = ?', [$ws['id']]);
                    $terdaftar = (int)($countRow['cnt'] ?? 0);
                    $sisa = max(0, $kapasitas - $terdaftar);
                    $isFull = $kapasitas > 0 ? ($sisa <= 0) : false;
                    $tanggalTs = !empty($ws['tanggal']) ? strtotime($ws['tanggal'] . (!empty($ws['waktu']) ? ' ' . $ws['waktu'] : '')) : null;
                    $isPast = $tanggalTs ? ($tanggalTs < time()) : false;
                    $tanggalFormatted = !empty($ws['tanggal']) ? date('d M Y', strtotime($ws['tanggal'])) : '-';
                    $isGratis = (float)($ws['biaya'] ?? 0) <= 0;
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="lomba-card">
                            <?php if (!empty($ws['banner_path'])): ?>
                                <div class="lomba-image mb-3">
                                    <img src="<?= htmlspecialchars($ws['banner_path']) ?>"
                                        alt="<?= htmlspecialchars($ws['judul']) ?>"
                                        class="img-fluid rounded shadow-sm"
                                        style="width: 100%; height: 200px; object-fit: cover;">
                                </div>
                            <?php else: ?>
                                <div class="lomba-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                            <?php endif; ?>
                            <h5 class="lomba-title"><?= htmlspecialchars($ws['judul']) ?></h5>
                            <p class="lomba-description">
                                <strong>Pemateri:</strong> <?= htmlspecialchars($ws['pemateri'] ?? '-') ?><br>
                                <i class="fas fa-calendar me-1 text-primary"></i> <?= htmlspecialchars($tanggalFormatted) ?>
                                <span class="ms-2"><i class="fas fa-clock me-1 text-primary"></i> <?= htmlspecialchars($ws['waktu'] ?? '-') ?></span><br>
                                <i class="fas fa-map-marker-alt me-1 text-primary"></i> <?= htmlspecialchars($ws['lokasi'] ?? '-') ?><br>
                                <span class="text-muted">Sisa kuota: <?= $sisa ?><?= $kapasitas ? ' / ' . $kapasitas : '' ?></span>
                                <?php if ($isGratis): ?>
                                    <span class="ms-2 badge bg-success">Gratis</span>
                                <?php else: ?>
                                    <span class="ms-2 badge bg-success">Rp <?= number_format((float)($ws['biaya'] ?? 0), 0, ',', '.') ?></span>
                                <?php endif; ?>
                            </p>
                            <div class="lomba-actions">
                                <?php
                                $detailHref = '';
                                $detailText = '';
                                if (!empty($ws['materi_pdf_path'])) {
                                    $detailHref = htmlspecialchars($ws['materi_pdf_path']);
                                    $detailText = 'Materi';
                                } else {
                                    $detailHref = 'detail_webinar.php?id=' . urlencode($ws['id']);
                                    $detailText = 'Detail Webinar';
                                }
                                ?>
                                <a href="<?= $detailHref ?>" class="btn-detail" <?= (!empty($ws['materi_pdf_path'])) ? 'target="_blank" rel="noopener"' : '' ?>>
                                    <i class="fas fa-info-circle"></i> <?= $detailText ?>
                                </a>
                                <?php if ($auth->isLoggedIn()): ?>
                                    <?php if ($isPast): ?>
                                        <a class="btn-daftar disabled" tabindex="-1" aria-disabled="true">
                                            <i class="fas fa-ban"></i> Selesai
                                        </a>
                                    <?php elseif ($isFull): ?>
                                        <a class="btn-daftar disabled" tabindex="-1" aria-disabled="true">
                                            <i class="fas fa-users-slash"></i> Kuota Penuh
                                        </a>
                                    <?php else: ?>
                                        <a href="webinar_register.php?webinar_id=<?= $ws['id'] ?>" class="btn-daftar">
                                            <i class="fas fa-plus"></i> Daftar Webinar
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn-daftar">
                                        <i class="fas fa-sign-in-alt"></i> Login untuk Daftar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Prize Pool Section -->
    <section id="hadiah" class="prize-section">
        <div class="container">
            <div class="text-center">
                <h2 class="mb-4">
                    <i class="fas fa-trophy"></i> Prize Pool
                </h2>

                <div class="total-prize">
                    <h1 class="display-2 fw-bold text-white mb-3">Rp 30.000.000</h1>
                    <p class="lead text-white-50 mb-4">Total Prize Pool untuk semua kategori lomba</p>
                    <p class="text-white-75">Hadiah menarik menanti para pemenang di setiap kategori lomba!</p>
                </div>
            </div>
        </div>
    </section>

    <?php if ($auth->isLoggedIn()): ?>
        <!-- Lomba Saya Section -->
        <section id="lomba-saya" class="py-5" style="background: #f8f9fa;">
            <div class="container">
                <h2 class="text-center mb-5">
                    <i class="fas fa-list text-primary"></i> Lomba Saya
                </h2>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon stats-pending">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3><?php echo count(array_filter($userPendaftaran, function ($p) {
                                    return $p['status'] === 'pending';
                                })); ?></h3>
                            <p class="text-muted mb-0">Menunggu Approval</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon stats-approved">
                                <i class="fas fa-check"></i>
                            </div>
                            <h3><?php echo count(array_filter($userPendaftaran, function ($p) {
                                    return $p['status'] === 'confirmed';
                                })); ?></h3>
                            <p class="text-muted mb-0">Diterima</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-icon stats-rejected">
                                <i class="fas fa-times"></i>
                            </div>
                            <h3><?php echo count(array_filter($userPendaftaran, function ($p) {
                                    return $p['status'] === 'rejected';
                                })); ?></h3>
                            <p class="text-muted mb-0">Ditolak</p>
                        </div>
                    </div>
                </div>

                <!-- Pendaftaran List -->
                <div class="row">
                    <div class="col-12">
                        <?php if (empty($userPendaftaran)): ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <h4>Belum ada pendaftaran lomba</h4>
                                <p class="lead">Anda belum mendaftar ke lomba apapun. Silakan daftar ke lomba yang tersedia.</p>
                                <a href="#kategori" class="btn btn-daftar btn-lg">
                                    Daftar Lomba Sekarang
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($userPendaftaran as $pendaftaran): ?>
                                <div class="pendaftaran-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="mb-3"><?php echo htmlspecialchars($pendaftaran['nama_lomba']); ?></h4>
                                            <p class="text-muted mb-3"><?php echo htmlspecialchars($pendaftaran['deskripsi']); ?></p>

                                            <div class="pendaftaran-details">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-calendar"></i> Tanggal Pendaftaran</h6>
                                                        <p><?php echo date('d/m/Y H:i', strtotime($pendaftaran['tanggal_daftar'])); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-id-card"></i> ID Pendaftar</h6>
                                                        <p><?php echo htmlspecialchars($pendaftaran['pendaftar_id']); ?></p>
                                                    </div>
                                                </div>

                                                <?php if ($pendaftaran['tanggal_approval']): ?>
                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
                                                            <h6><i class="fas fa-clock"></i> Tanggal Diperiksa</h6>
                                                            <p><?php echo date('d/m/Y H:i', strtotime($pendaftaran['tanggal_approval'])); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($pendaftaran['catatan_admin']): ?>
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6><i class="fas fa-comment"></i> Catatan Admin</h6>
                                                            <p class="text-info"><?php echo htmlspecialchars($pendaftaran['catatan_admin']); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <span class="status-badge status-<?php echo $pendaftaran['status']; ?>">
                                                <?php
                                                switch ($pendaftaran['status']) {
                                                    case 'pending':
                                                        echo '<i class="fas fa-clock"></i> Menunggu Approval';
                                                        break;
                                                    case 'confirmed':
                                                        echo '<i class="fas fa-check"></i> Diterima';
                                                        break;
                                                    case 'rejected':
                                                        echo '<i class="fas fa-times"></i> Ditolak';
                                                        break;
                                                }
                                                ?>
                                            </span>

                                            <div class="mt-3 d-grid gap-2">
                                                <!-- Tombol Detail Lomba Saya -->
                                                <a href="detail_lomba_saya.php" class="btn btn-primary">
                                                    <i class="fas fa-eye me-1"></i> Detail Lomba Saya
                                                </a>

                                                <?php if ($pendaftaran['status'] === 'pending'): ?>
                                                    <small class="text-muted text-center">
                                                        <i class="fas fa-info-circle"></i>
                                                        Admin akan memeriksa pendaftaran Anda dalam waktu 1-3 hari kerja
                                                    </small>
                                                <?php elseif ($pendaftaran['status'] === 'confirmed'): ?>
                                                    <?php if (!empty($pendaftaran['link_grup_wa'])): ?>
                                                        <a href="<?= htmlspecialchars($pendaftaran['link_grup_wa']) ?>" target="_blank" class="btn btn-success">
                                                            <i class="fab fa-whatsapp me-1"></i> Gabung Grup WhatsApp
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Webinar Saya Section -->
        <?php
        $currentUser = $auth->getCurrentUser();
        $userWebinarRegs = $db->fetchAll('SELECT wp.*, w.judul, w.tanggal, w.waktu, w.lokasi, w.biaya FROM b_webinar_pendaftar wp JOIN b_webinar w ON wp.webinar_id = w.id WHERE wp.user_id = ? ORDER BY wp.created_at DESC', [$currentUser['id']]);
        ?>
        <section id="webinar-saya" class="py-5" style="background: #ffffff;">
            <div class="container">
                <h2 class="text-center mb-5">
                    <i class="fas fa-chalkboard-teacher text-primary"></i> Webinar Saya
                </h2>
                <div class="row">
                    <div class="col-12">
                        <?php if (empty($userWebinarRegs)): ?>
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <h4>Belum ada pendaftaran webinar</h4>
                                <p class="lead">Anda belum mendaftar ke webinar apapun. Silakan pilih webinar yang tersedia.</p>
                                <a href="#webinar" class="btn btn-daftar btn-lg">
                                    Lihat Webinar
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($userWebinarRegs as $wr): ?>
                                <div class="pendaftaran-card">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h4 class="mb-2"><?php echo htmlspecialchars($wr['judul']); ?></h4>
                                            <p class="text-muted mb-3">
                                                <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($wr['lokasi'] ?? '-'); ?>
                                                <span class="ms-3"><i class="fas fa-calendar me-1"></i> <?php echo htmlspecialchars($wr['tanggal'] ?? '-'); ?> <?php echo htmlspecialchars($wr['waktu'] ?? ''); ?></span>
                                            </p>
                                            <div class="pendaftaran-details">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-id-card"></i> ID Pendaftaran</h6>
                                                        <p><?php echo htmlspecialchars($wr['id']); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6><i class="fas fa-money-bill"></i> Biaya</h6>
                                                        <p>Rp <?php echo number_format((float)$wr['biaya'], 0, ',', '.'); ?></p>
                                                    </div>
                                                </div>
                                                <?php if (!empty($wr['metode_pembayaran'])): ?>
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            <h6><i class="fas fa-receipt"></i> Metode Pembayaran</h6>
                                                            <p><?php echo htmlspecialchars($wr['metode_pembayaran']); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <span class="status-badge status-<?php echo $wr['status']; ?>">
                                                <?php
                                                switch ($wr['status']) {
                                                    case 'pending':
                                                        echo '<i class="fas fa-clock"></i> Pending';
                                                        break;
                                                    case 'approved':
                                                        echo '<i class="fas fa-check"></i> Disetujui';
                                                        break;
                                                    case 'rejected':
                                                        echo '<i class="fas fa-times"></i> Ditolak';
                                                        break;
                                                }
                                                ?>
                                            </span>
                                            <div class="mt-3 d-grid gap-2">
                                                <?php if ($wr['status'] !== 'rejected'): ?>
                                                    <?php if (empty($wr['bukti_transfer'])): ?>
                                                        <a href="webinar_payment.php?id=<?php echo urlencode($wr['id']); ?>" class="btn btn-primary">
                                                            <i class="fas fa-arrow-right"></i> Lanjut Pembayaran
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo htmlspecialchars($wr['bukti_transfer']); ?>" target="_blank" class="btn btn-outline-secondary">
                                                            <i class="fas fa-file-image"></i> Lihat Bukti
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Modal: Already Registered -->
    <?php if ($auth->isLoggedIn()): ?>
        <div class="modal fade modal-celebrate" id="alreadyRegisteredModal" tabindex="-1" aria-labelledby="alreadyRegisteredModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient">
                        <h5 class="modal-title" id="alreadyRegisteredModalLabel">
                            <span class="title-icon"><i class="fas fa-check"></i></span>
                            Sudah Terdaftar
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body modal-body-pretty">
                        <div class="big-icon"><i class="fas fa-user-check"></i></div>
                        <h5>Yeay! Kamu sudah terdaftar 🎉</h5>
                        <p>Silakan cek progres kamu di bagian <a href="#lomba-saya">Lomba Saya</a>.</p>
                    </div>
                    <div class="modal-footer modal-footer-soft d-flex justify-content-between px-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Nanti Saja</button>
                        <a href="#lomba-saya" class="btn btn-purple"><i class="fas fa-list me-1"></i> Lihat Lomba Saya</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="row">
                <div class="row">
                    <!-- Brand & Description -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="footer-widget">
                            <h5 class="footer-title">
                                <i class="fas fa-trophy me-2"></i> Bluvocation Creative Fest
                            </h5>
                            <p class="footer-description">
                                Platform lomba kreatif untuk siswa SD/SMP/SMA/SMK/Sederajat yang mengembangkan bakat dan kreativitas
                                dalam berbagai bidang teknologi dan desain.
                            </p>
                            <div class="social-links">
                                <a href="https://www.instagram.com/bluvocationfest?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="
                                    target="_blank"
                                    class="social-link"
                                    aria-label="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Lomba Links -->
                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="footer-widget">
                            <h6 class="footer-subtitle">Lomba</h6>
                            <ul class="footer-links">
                                <li><a href="#kategori">Kategori Lomba</a></li>
                                <li><a href="#timeline">Timeline</a></li>
                                <li><a href="#hadiah">Hadiah</a></li>
                                <li><a href="register.php">Daftar Lomba</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="footer-widget">
                            <h6 class="footer-subtitle">Kontak</h6>
                            <div class="contact-info">
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <span>SMK Budi Luhur Kota Tangerang</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-phone me-2"></i>
                                    <span>+62 877 2978 9002</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:bluvocationfest@gmail.com" class="text-light text-decoration-none">
                                        bluvocationfest@gmail.com
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="footer-divider">

                <div class="footer-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="copyright-text">
                                &copy; 2025 Bluvocation Creative Fest. All rights reserved.
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <ul class="footer-bottom-links">
                                <li><a href="#">Privacy Policy</a></li>
                                <li><a href="#">Terms of Service</a></li>
                                <li><a href="#">FAQ</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
    </footer>

    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($auth->isLoggedIn()): ?>
            // Pemetaan kategori yang sudah didaftarkan user dari PHP ke JS
            const userRegisteredCategoryIds = <?php
                                                $registeredIds = [];
                                                if (!empty($userPendaftaran)) {
                                                    foreach ($userPendaftaran as $p) {
                                                        $registeredIds[] = (int)$p['kategori_lomba_id'];
                                                    }
                                                }
                                                echo json_encode($registeredIds);
                                                ?>;

            // Intercept klik tombol daftar untuk mencegah redirect jika sudah terdaftar
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.btn-daftar-lomba').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        const kategoriId = parseInt(this.getAttribute('data-kategori'));
                        if (userRegisteredCategoryIds.includes(kategoriId)) {
                            e.preventDefault();
                            const modalEl = document.getElementById('alreadyRegisteredModal');
                            if (modalEl) {
                                const m = new bootstrap.Modal(modalEl);
                                m.show();
                            }
                        }
                    });
                });
            });
        <?php endif; ?>
    </script>
</body>

</html>

