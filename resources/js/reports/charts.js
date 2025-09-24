// Charts para el módulo de reportes

// Inicializar chart de resumen de pagos
window.initResumenPagosChart = function (data) {
    const ctx = document.getElementById("estadoCobrosChart");
    if (ctx && data) {
        const pagados = data.cobros_pagados || 0;
        const pendientes = (data.total_cobros || 0) - pagados;

        new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["Pagados", "Pendientes"],
                datasets: [
                    {
                        data: [pagados, pendientes],
                        backgroundColor: [
                            "rgb(34, 197, 94)", // Green
                            "rgb(249, 115, 22)", // Orange
                        ],
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            padding: 10,
                            fontSize: 12,
                            color: document.documentElement.classList.contains(
                                "dark"
                            )
                                ? "#e5e7eb"
                                : "#374151",
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const total = context.dataset.data.reduce(
                                    (a, b) => a + b,
                                    0
                                );
                                const percentage = (
                                    (context.parsed / total) *
                                    100
                                ).toFixed(1);
                                return (
                                    context.label +
                                    ": " +
                                    context.parsed +
                                    " (" +
                                    percentage +
                                    "%)"
                                );
                            },
                        },
                    },
                },
                cutout: "60%",
            },
        });
    }
};

// Inicializar chart de análisis de atrasos
window.initAnalisisAtrasosChart = function (data) {
    const ctx = document.getElementById("distribucionAtrasosChart");
    if (ctx && data && data.distribucion) {
        const menos15 = data.distribucion.menos_15 || 0;
        const entre15_30 = data.distribucion.entre_15_30 || 0;
        const mas30 = data.distribucion.mas_30 || 0;

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["< 15 días", "15-30 días", "> 30 días"],
                datasets: [
                    {
                        data: [menos15, entre15_30, mas30],
                        backgroundColor: [
                            "rgb(234, 179, 8)", // Yellow
                            "rgb(249, 115, 22)", // Orange
                            "rgb(239, 68, 68)", // Red
                        ],
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: document.documentElement.classList.contains(
                                "dark"
                            )
                                ? "#9ca3af"
                                : "#6b7280",
                        },
                        grid: {
                            color: document.documentElement.classList.contains(
                                "dark"
                            )
                                ? "#374151"
                                : "#e5e7eb",
                        },
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains(
                                "dark"
                            )
                                ? "#9ca3af"
                                : "#6b7280",
                        },
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        });
    }
};

// Inicializar chart de proyección temporal
window.initProyeccionTemporalChart = function (data) {
    const ctx = document.getElementById("proyeccionTemporalChart");
    if (ctx && data) {
        const periodos = data.periodos || [];
        const proyecciones = data.proyecciones || [];
        const actuales = data.actuales || [];

        new Chart(ctx, {
            type: "line",
            data: {
                labels: periodos,
                datasets: [
                    {
                        label: "Ingresos Reales",
                        data: actuales,
                        borderColor: "rgb(59, 130, 246)",
                        backgroundColor: "rgba(59, 130, 246, 0.1)",
                        tension: 0.4,
                        fill: true,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                    },
                    {
                        label: "Proyección",
                        data: proyecciones,
                        borderColor: "rgb(34, 197, 94)",
                        backgroundColor: "rgba(34, 197, 94, 0.1)",
                        tension: 0.4,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top",
                        labels: {
                            color: document.documentElement.classList.contains(
                                "dark"
                            )
                                ? "#e5e7eb"
                                : "#374151",
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return (
                                    context.dataset.label +
                                    ": S/" +
                                    new Intl.NumberFormat("es-PE").format(
                                        context.parsed.y
                                    )
                                );
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: document.documentElement.classList.contains(
                                "dark"
                            )
                                ? "#9ca3af"
                                : "#6b7280",
                            callback: function (value) {
                                return (
                                    "S/" +
                                    new Intl.NumberFormat("es-PE").format(value)
                                );
                            },
                        },
                        grid: {
                            color: document.documentElement.classList.contains(
                                "dark"
                            )
                                ? "#374151"
                                : "#e5e7eb",
                        },
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains(
                                "dark"
                            )
                                ? "#9ca3af"
                                : "#6b7280",
                        },
                        grid: {
                            color: document.documentElement.classList.contains(
                                "dark"
                            )
                                ? "#374151"
                                : "#e5e7eb",
                        },
                    },
                },
                interaction: {
                    intersect: false,
                    mode: "index",
                },
            },
        });
    }
};
