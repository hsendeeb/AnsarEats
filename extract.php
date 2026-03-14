<?php
$dashboardPath = 'c:\\Users\\TopComputers\\Herd\\firstProject1\\resources\\views\\owner\\dashboard.blade.php';
if (!file_exists($dashboardPath)) {
    echo "Dashboard not found.\n";
    exit;
}

$dashboard = file_get_contents($dashboardPath);

$startStr = "<!-- Orders Management -->";
$endTagStr = "<!-- Categories & Items List -->";
$endIndex = strpos($dashboard, $endTagStr);
$startIndex = strpos($dashboard, $startStr);

if ($startIndex === false || $endIndex === false) {
    echo "Indices not found.\n";
    exit;
}

$ordersContent = substr($dashboard, $startIndex, $endIndex - $startIndex);

// Delete it from dashboard and insert a link instead
$linkHtml = "<!-- Orders Management Link -->
            <div class=\"mb-16 bg-white rounded-3xl border border-gray-100 shadow-sm p-8 flex items-center justify-between\">
                <div>
                    <h3 class=\"text-2xl font-black outfit text-gray-900 flex items-center gap-3\">
                        <span class=\"w-2 h-8 bg-emerald-500 rounded-full\"></span>
                        Incoming Orders
                    </h3>
                    <p class=\"text-gray-500 font-medium mt-1\">Manage and track your restaurant's orders in real-time.</p>
                </div>
                <a href=\"{{ route('owner.orders') }}\" class=\"inline-flex items-center gap-2 px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 active:scale-95\">
                    View Orders
                    <svg class=\"w-5 h-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2.5\" d=\"M17 8l4 4m0 0l-4 4m4-4H3\"></path></svg>
                </a>
            </div>

            ";

$newDashboard = substr_replace($dashboard, $linkHtml, $startIndex, $endIndex - $startIndex);
file_put_contents($dashboardPath, $newDashboard);

// Now wrap it in a proper layout
$newOrdersFile = "@extends('layouts.app')\n\n@section('content')\n<div class=\"min-h-screen bg-gray-50 py-10 px-4\">\n    <div class=\"max-w-7xl mx-auto\">\n        <div class=\"mb-6\">\n            <a href=\"{{ route('owner.dashboard') }}\" class=\"inline-flex items-center gap-2 text-gray-500 hover:text-emerald-600 font-bold transition-colors bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm\">\n                <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2.5\" d=\"M10 19l-7-7m0 0l7-7m-7 7h18\"></path></svg>\n                Back to Dashboard\n            </a>\n        </div>\n\n" . $ordersContent . "\n    </div>\n</div>\n@endsection\n";

file_put_contents('c:\\Users\\TopComputers\\Herd\\firstProject1\\resources\\views\\owner\\orders.blade.php', $newOrdersFile);
echo "Extraction completed successfully.\n";
