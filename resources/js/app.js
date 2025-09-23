// import "./bootstrap";

// Import Chart.js
// import { Chart } from "chart.js";

// Import flatpickr
// import flatpickr from "flatpickr";

document.addEventListener("DOMContentLoaded", () => {
    // Light switcher
    const lightSwitches = document.querySelectorAll(".light-switch");
    if (lightSwitches.length > 0) {
        lightSwitches.forEach((lightSwitch, i) => {
            if (localStorage.getItem("dark-mode") === "true") {
                lightSwitch.checked = true;
            }
            lightSwitch.addEventListener("change", () => {
                const { checked } = lightSwitch;
                lightSwitches.forEach((el, n) => {
                    if (n !== i) {
                        el.checked = checked;
                    }
                });
                document.documentElement.classList.add(
                    "[&_*]:!transition-none"
                );
                if (lightSwitch.checked) {
                    document.documentElement.classList.add("dark");
                    document.querySelector("html").style.colorScheme = "dark";
                    localStorage.setItem("dark-mode", true);
                    document.dispatchEvent(
                        new CustomEvent("darkMode", { detail: { mode: "on" } })
                    );
                } else {
                    document.documentElement.classList.remove("dark");
                    document.querySelector("html").style.colorScheme = "light";
                    localStorage.setItem("dark-mode", false);
                    document.dispatchEvent(
                        new CustomEvent("darkMode", { detail: { mode: "off" } })
                    );
                }
                setTimeout(() => {
                    document.documentElement.classList.remove(
                        "[&_*]:!transition-none"
                    );
                }, 1);
            });
        });
    }
    // Flatpickr
    // flatpickr(".datepicker", {
    //     mode: "range",
    //     static: true,
    //     monthSelectorType: "static",
    //     dateFormat: "M j, Y",
    //     defaultDate: [new Date().setDate(new Date().getDate() - 6), new Date()],
    //     prevArrow:
    //         '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M5.4 10.8l1.4-1.4-4-4 4-4L5.4 0 0 5.4z" /></svg>',
    //     nextArrow:
    //         '<svg class="fill-current" width="7" height="11" viewBox="0 0 7 11"><path d="M1.4 10.8L0 9.4l4-4-4-4L1.4 0l5.4 5.4z" /></svg>',
    //     onReady: (selectedDates, dateStr, instance) => {
    //         instance.element.value = dateStr.replace("to", "-");
    //         const customClass = instance.element.getAttribute("data-class");
    //         instance.calendarContainer.classList.add(customClass);
    //     },
    //     onChange: (selectedDates, dateStr, instance) => {
    //         instance.element.value = dateStr.replace("to", "-");
    //     },
    // });
});
