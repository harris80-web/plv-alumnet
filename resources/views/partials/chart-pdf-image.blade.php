{{--
    chartCanvasToPdfImage(canvas): a Chart.js canvas as a JPEG data URL for the
    "EXPORT PDF" buttons. JPEG because DomPDF can embed it without the PHP GD
    extension (PNGs need GD); drawn on white so transparent areas don't turn
    black. Returns null if the canvas can't be read, and the PDF then shows
    that chart as a table. The server side is App\Services\PdfChartImages.
--}}
<script>
    function chartCanvasToPdfImage(canvas) {
        try {
            const flat = document.createElement('canvas');
            flat.width = canvas.width;
            flat.height = canvas.height;
            const ctx = flat.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, flat.width, flat.height);
            ctx.drawImage(canvas, 0, 0);
            return flat.toDataURL('image/jpeg', 0.92);
        } catch (e) {
            return null;
        }
    }
</script>
