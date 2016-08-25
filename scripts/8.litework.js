$(function() {
    $('#menu').slicknav();
});

// Load Addthis share bar after page load onload();
function downloadJSAtOnload() {
    var element = document.createElement("script");
    element.src = "//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-57bec894bfacf33c";
    document.body.appendChild(element);
}
if (window.addEventListener)
    window.addEventListener("load", downloadJSAtOnload, false);
else if (window.attachEvent)
    window.attachEvent("onload", downloadJSAtOnload);
else window.onload = downloadJSAtOnload;
