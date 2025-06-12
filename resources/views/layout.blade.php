<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            @apply bg-white rounded-lg shadow-lg p-6 mb-6;
        }
        .status-badge {
            @apply px-3 py-1 rounded-full text-sm font-semibold;
        }
        .status-active {
            @apply bg-green-100 text-green-800;
        }
        .status-inactive {
            @apply bg-red-100 text-red-800;
        }
        .metric-card {
            @apply bg-white rounded-lg shadow p-4;
        }
        .metric-value {
            @apply text-2xl font-bold text-gray-800;
        }
        .metric-label {
            @apply text-sm text-gray-600;
        }
    </style>
</head>
<body class="bg-gray-100">
<nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <i class="fas fa-server text-2xl text-indigo-600"></i>
                    <span class="ml-2 text-xl font-bold">System Monitor</span>
                </div>
            </div>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @yield('content')
</main>
</body>
</html>