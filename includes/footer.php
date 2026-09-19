</div>
    </main>
    
    <?php if (empty($in_admin)): ?>
    
    <?php include_once __DIR__ . '/chat_widget.php'; ?>

    <?php 
    include_once __DIR__ . '/counter.php';
    if (!isset($conn) || !$conn) {
        require_once __DIR__ . '/db_config.php';
        $conn = getDbConnection();
    }
    $sg_traffic = recordVisitorTraffic($conn);
    ?>

    <style>
    .sg-footer-counter-bar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 12px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 30px;
        padding: 6px 20px;
        font-size: 13px;
        color: #e2e8f0;
        margin-bottom: 8px;
    }
    .sg-counter-item i {
        margin-right: 4px;
        opacity: 0.85;
    }
    .sg-counter-online i {
        color: #22c55e;
        font-size: 9px;
    }
    .sg-counter-sep {
        color: rgba(255, 255, 255, 0.3);
    }
    </style>

    <footer class="site-footer">
        <div class="container text-center">
            <div class="sg-footer-counter-bar">
                <span class="sg-counter-item"><i class="fa-solid fa-chart-line"></i> Tổng truy cập: <strong><?php echo number_format($sg_traffic['total']); ?></strong></span>
                <span class="sg-counter-sep">•</span>
                <span class="sg-counter-item"><i class="fa-solid fa-calendar-day"></i> Hôm nay: <strong><?php echo number_format($sg_traffic['today']); ?></strong></span>
                <span class="sg-counter-sep">•</span>
                <span class="sg-counter-item sg-counter-online"><i class="fa-solid fa-circle"></i> Đang online: <strong><?php echo number_format($sg_traffic['online']); ?></strong></span>
            </div>
            <p class="mt-2 mb-0">&copy; 2026 Shop Hoa Tươi Sen Garden. All rights reserved. Địa chỉ: 280 An Dương Vương, P.4, Q.5, TP.HCM | Hotline: 0989 366 990</p>
        </div>
    </footer>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>