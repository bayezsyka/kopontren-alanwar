<style>
    /* ========== MODERN DESIGN SYSTEM ========== */
    
    /* Loading spinner with modern animation */
    .spinner {
        border: 3px solid rgba(6, 182, 212, 0.1);
        border-top: 3px solid #06b6d4;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        margin: 0 auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Smooth fade-in animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Scale animation */
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Toast notifications with modern design */
    .toast {
        position: fixed;
        bottom: 80px;
        right: 16px;
        left: 16px;
        max-width: 420px;
        background: white;
        padding: 1rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        transform: translateY(200px);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 9999;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(229, 231, 235, 0.5);
    }

    @media (min-width: 640px) {
        .toast {
            left: auto;
            bottom: 24px;
        }
    }

    .toast.show {
        transform: translateY(0);
    }

    .toast.success { 
        border-left: 4px solid #10b981;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(255, 255, 255, 0.95));
    }
    .toast.error { 
        border-left: 4px solid #ef4444;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(255, 255, 255, 0.95));
    }
    .toast.info { 
        border-left: 4px solid #3b82f6;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(255, 255, 255, 0.95));
    }
    .toast.warning { 
        border-left: 4px solid #f59e0b;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(255, 255, 255, 0.95));
    }

    /* Modern button system */
    .btn {
        @apply px-6 py-3.5 rounded-xl font-semibold transition-all duration-200 min-h-[52px] flex items-center justify-center text-center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transform: translateY(0);
    }

    .btn:active {
        transform: translateY(1px);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-block {
        @apply w-full;
    }

    .btn-primary {
        @apply bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white;
        box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
    }

    .btn-primary:hover {
        box-shadow: 0 6px 16px rgba(6, 182, 212, 0.4);
    }

    .btn-secondary {
        @apply bg-white hover:bg-gray-50 text-gray-800 border border-gray-200;
    }

    .btn-danger {
        @apply bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .btn-success {
        @apply bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-sm {
        @apply px-4 py-2.5 min-h-[44px] text-sm rounded-lg;
    }

    /* Enhanced input styles */
    .input {
        @apply w-full px-4 py-3.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 min-h-[52px] text-base bg-white;
        transition: all 0.2s ease;
    }

    .input:focus {
        box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.1);
    }

    .input-sm {
        @apply px-3.5 py-2.5 min-h-[44px] text-sm;
    }

    /* Premium card design */
    .card {
        @apply bg-white rounded-2xl shadow-sm p-5 border border-gray-100;
        transition: all 0.3s ease;
        animation: fadeIn 0.5s ease;
    }

    .card:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    /* Modern badges */
    .badge {
        @apply inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold;
        animation: scaleIn 0.3s ease;
    }

    .badge-success {
        @apply bg-green-100 text-green-700;
    }

    .badge-danger {
        @apply bg-red-100 text-red-700;
    }

    .badge-warning {
        @apply bg-yellow-100 text-yellow-700;
    }

    .badge-info {
        @apply bg-blue-100 text-blue-700;
    }

    /* Premium loading overlay */
    .loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-center;
        z-index: 9998;
        animation: fadeIn 0.2s ease;
    }

    /* Prevent scroll when modal open */
    body.modal-open {
        overflow: hidden;
    }

    /* Smooth transitions for all interactive elements */
    a, button, input, select, textarea {
        transition: all 0.2s ease;
    }

    /* Enhanced table styles */
    table {
        @apply w-full;
    }

    thead {
        @apply bg-gradient-to-r from-gray-50 to-gray-100;
    }

    th {
        @apply px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider;
    }

    td {
        @apply px-4 py-3 text-sm;
    }

    tbody tr {
        @apply border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150;
    }

    /* Gradient backgrounds for stat cards */
    .stat-card {
        background: linear-gradient(135deg, var(--from-color), var(--to-color));
        border-radius: 1rem;
        padding: 1.5rem;
        color: white;
        box-shadow: 0 8px 20px -6px var(--shadow-color);
        transition: all 0.3s ease;
        animation: scaleIn 0.4s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px -8px var(--shadow-color);
    }

    /* Responsive utilities */
    @media (max-width: 640px) {
        .btn {
            @apply min-h-[48px] py-3;
        }
        
        .input {
            @apply min-h-[48px];
        }

        .card {
            @apply p-4 rounded-xl;
        }
    }

    /* Add smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }

    /* Enhanced focus states for accessibility */
    *:focus-visible {
        outline: 2px solid #06b6d4;
        outline-offset: 2px;
    }

    /* Glassmorphism effect */
    .glass {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Skeleton loading animation */
    .skeleton {
        background: linear-gradient(
            90deg,
            #f0f0f0 25%,
            #e0e0e0 50%,
            #f0f0f0 75%
        );
        background-size: 200% 100%;
        animation: loading 1.5s ease-in-out infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }
</style>
