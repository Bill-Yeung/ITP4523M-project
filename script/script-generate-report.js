// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    // Check if chart data exists and canvas element exists
    if (typeof chartData === 'undefined') {
        console.error('Chart data is not defined');
        return;
    }

    const canvas = document.getElementById('barChart');
    if (!canvas) {
        console.error('Canvas element not found');
        return;
    }

    const ctx = canvas.getContext('2d');
    const padding = 80;
    const axisLabelFont = "16px Arial";
    const tickFont = "12px Arial";
    const labelColor = "#333";
    const barColors = ["#4CAF50", "#2196F3", "#FF9800", "#E91E63", "#9C27B0", "#00BCD4", "#FF5722"];
    const shadowColor = "rgba(0,0,0,0.2)";
    const backgroundColor = "#f9f9f9";
    
    canvas.style.background = backgroundColor;
    const chartWidth = canvas.width - padding * 2;
    const chartHeight = canvas.height - padding * 2;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (!chartData || chartData.length === 0) {
        ctx.fillStyle = labelColor;
        ctx.font = "20px Arial";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText("No data available for chart", canvas.width/2, canvas.height/2);
        return;
    }

    // Calculate max value
    const maxTotalRaw = Math.max(...chartData.map(d => parseFloat(d.total) || 0));
    
    // Round maxTotal up to nearest nice number
    const roundTo = (value) => {
        if (value === 0) return 10;
        const magnitude = Math.pow(10, Math.floor(Math.log10(value)));
        const factor = value / magnitude;
        let rounded;
        if (factor <= 1) rounded = 1;
        else if (factor <= 2) rounded = 2;
        else if (factor <= 5) rounded = 5;
        else rounded = 10;
        return rounded * magnitude;
    };
    
    const maxTotal = roundTo(maxTotalRaw);

    // Calculate bar dimensions
    const barWidth = Math.min(80, (chartWidth / chartData.length) * 0.6);
    const barSpacing = (chartWidth - (barWidth * chartData.length)) / (chartData.length + 1);

    // Draw axes
    ctx.strokeStyle = labelColor;
    ctx.lineWidth = 2;
    ctx.beginPath();
    // Y axis
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, canvas.height - padding);
    // X axis
    ctx.lineTo(canvas.width - padding, canvas.height - padding);
    ctx.stroke();

    // Draw Y axis labels and grid lines
    ctx.fillStyle = labelColor;
    ctx.textAlign = "right";
    ctx.textBaseline = "middle";
    ctx.font = tickFont;

    const yStepCount = 5;
    for (let i = 0; i <= yStepCount; i++) {
        const yVal = (maxTotal / yStepCount) * i;
        const yPos = canvas.height - padding - (chartHeight / maxTotal) * yVal;

        // Y axis label
        ctx.fillText("$" + yVal.toFixed(0), padding - 10, yPos);

        // Grid line
        if (i > 0) {
            ctx.strokeStyle = "#e0e0e0";
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(padding, yPos);
            ctx.lineTo(canvas.width - padding, yPos);
            ctx.stroke();
        }
    }

    // Draw axis labels
    ctx.fillStyle = labelColor;
    ctx.textAlign = "center";
    ctx.textBaseline = "top";
    ctx.font = axisLabelFont;
    ctx.fillText("Products", canvas.width / 2, canvas.height - padding + 50);

    // Y axis label (rotated)
    ctx.save();
    ctx.translate(20, canvas.height / 2);
    ctx.rotate(-Math.PI / 2);
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText("Total Revenue ($)", 0, 0);
    ctx.restore();

    // Draw bars
    chartData.forEach((item, index) => {
        const x = padding + barSpacing + index * (barWidth + barSpacing);
        const barHeight = (parseFloat(item.total) / maxTotal) * chartHeight;
        const y = canvas.height - padding - barHeight;

        // Draw shadow
        ctx.shadowColor = shadowColor;
        ctx.shadowBlur = 4;
        ctx.shadowOffsetX = 2;
        ctx.shadowOffsetY = 2;

        // Draw bar with color
        ctx.fillStyle = barColors[index % barColors.length];
        ctx.fillRect(x, y, barWidth, barHeight);

        // Reset shadow
        ctx.shadowColor = "transparent";
        ctx.shadowBlur = 0;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0;

        // Draw value label above bar
        ctx.fillStyle = labelColor;
        ctx.textAlign = "center";
        ctx.textBaseline = "bottom";
        ctx.font = "11px Arial";
        ctx.fillText("$" + parseFloat(item.total).toFixed(2), x + barWidth / 2, y - 5);

        // Draw product ID below bar
        ctx.textBaseline = "top";
        ctx.fillText("ID: " + item.id, x + barWidth / 2, canvas.height - padding + 5);
    });

    // Draw legend
    const legendX = canvas.width - 250;
    const legendY = padding;
    const legendWidth = 230;
    const lineHeight = 20;
    const legendHeight = chartData.length * lineHeight + 30;

    // Legend background
    ctx.fillStyle = "rgba(255, 255, 255, 0.95)";
    ctx.fillRect(legendX, legendY, legendWidth, legendHeight);
    ctx.strokeStyle = "#ccc";
    ctx.lineWidth = 1;
    ctx.strokeRect(legendX, legendY, legendWidth, legendHeight);

    // Legend title
    ctx.fillStyle = labelColor;
    ctx.font = "14px Arial";
    ctx.textAlign = "left";
    ctx.textBaseline = "top";
    ctx.fillText("Product Legend:", legendX + 10, legendY + 10);

    // Legend items
    ctx.font = "12px Arial";
    chartData.forEach((item, index) => {
        const itemY = legendY + 35 + index * lineHeight;
        
        // Color box
        ctx.fillStyle = barColors[index % barColors.length];
        ctx.fillRect(legendX + 10, itemY - 2, 12, 12);
        
        // Text
        ctx.fillStyle = labelColor;
        const productName = item.product.length > 15 ? item.product.substring(0, 15) + "..." : item.product;
        ctx.fillText(`${item.id}: ${productName}`, legendX + 28, itemY);
    });

    console.log('Chart rendered successfully with', chartData.length, 'items');
});