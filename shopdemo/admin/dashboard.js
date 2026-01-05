// Gọi API để lấy dữ liệu
fetch('get_data.php')
    .then(response => response.json())
    .then(data => {
        // Hiển thị đơn hàng mới
        const orderList = document.getElementById('orderList');
        data.orders.forEach(order => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item';
            listItem.textContent = `#${order.id} - ${order.username} - ${new Date(order.datetime).toLocaleString()}`;
            orderList.appendChild(listItem);
        });

        // Hiển thị sản phẩm bán chạy
        const productList = document.getElementById('productList');
        data.products.forEach(product => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item';
            listItem.textContent = `${product.name} - ${product.total_quantity} sản phẩm`;
            productList.appendChild(listItem);
        });

        // Biểu đồ tổng quan hệ thống
        const overviewCtx = document.getElementById('overviewChart').getContext('2d');
        new Chart(overviewCtx, {
            type: 'bar',
            data: {
                labels: ['Khách hàng', 'Sản phẩm', 'Đơn hàng', 'Bán chạy', 'Mới nhất'],
                datasets: [{
                    label: 'Số lượng',
                    data: [
                        data.customer_count,
                        data.product_count,
                        data.order_count,
                        data.top_product_quantity,
                        1 // Mặc định cho sản phẩm mới nhất
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Thống kê hệ thống'
                    }
                }
            }
        });
    })
    .catch(error => console.error('Error:', error));