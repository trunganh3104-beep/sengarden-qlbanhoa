<div class="sg-promo-overlay" id="sgPromoOverlay" style="display: none;">
    <div class="sg-promo-modal" id="sgPromoModal">
        <button type="button" class="sg-promo-close" id="btnPromoClose" aria-label="Đóng">&times;</button>
        <div class="sg-promo-header">
            <div class="sg-promo-badge"><i class="fa-solid fa-gift"></i> ƯU ĐÃI ĐẶC QUYỀN SEN GARDEN</div>
            <h3 class="sg-promo-title">Tặng Voucher 50.000đ & Freeship</h3>
            <p class="sg-promo-sub">Áp dụng cho tất cả các mẫu hoa tươi nghệ thuật đặt trực tuyến</p>
        </div>

        <div class="sg-promo-timer-box">
            <div class="sg-promo-timer-label">
                <i class="fa-solid fa-clock-rotate-left"></i> Lập lịch ưu đãi tự động kết thúc sau:
            </div>
            <div class="sg-promo-countdown" id="sgPromoCountdown">
                <div class="sg-cd-unit"><span id="cdHours">12</span><small>Giờ</small></div>
                <span class="sg-cd-sep">:</span>
                <div class="sg-cd-unit"><span id="cdMins">45</span><small>Phút</small></div>
                <span class="sg-cd-sep">:</span>
                <div class="sg-cd-unit"><span id="cdSecs">30</span><small>Giây</small></div>
            </div>
        </div>

        <div class="sg-promo-voucher-card">
            <div class="sg-voucher-info">
                <div class="sg-voucher-code" id="promoCodeText">HOATUOI2026</div>
                <div class="sg-voucher-desc">Giảm ngay 50.000đ đơn từ 350.000đ + Tặng kèm thiệp</div>
            </div>
            <button type="button" class="sg-btn-copy" id="btnCopyPromoCode" title="Bấm để sao chép">
                <i class="fa-regular fa-copy"></i> Sao chép
            </button>
        </div>

        <div class="sg-promo-actions">
            <a href="#danh-sach-hoa" class="sg-promo-btn-shop" id="btnPromoShop">
                <i class="fa-solid fa-bag-shopping"></i> Khám Phá Mẫu Hoa Ngay
            </a>
            <button type="button" class="sg-promo-btn-skip" id="btnPromoSkip">
                Để sau, xem tiếp mẫu hoa
            </button>
        </div>
    </div>
</div>

<button type="button" class="sg-floating-promo-btn" id="btnOpenPromoFloating" title="Bấm nhận ưu đãi giảm 50K">
    <span class="sg-floating-icon"><i class="fa-solid fa-gift"></i></span>
    <span class="sg-floating-text">Ưu Đãi Hot (Flash Sale)</span>
</button>

<style>
.sg-promo-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.68);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 9999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: sgFadeIn 0.3s ease forwards;
}

.sg-promo-modal {
    background: #ffffff;
    border-radius: 24px;
    width: 100%;
    max-width: 480px;
    padding: 32px 28px 26px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28), 0 0 0 1px rgba(244, 63, 94, 0.15);
    position: relative;
    text-align: center;
    animation: sgPopUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.sg-promo-close {
    position: absolute;
    top: 14px;
    right: 16px;
    background: #f1f5f9;
    border: none;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    font-size: 22px;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    transition: all 0.2s ease;
}
.sg-promo-close:hover {
    background: #fee2e2;
    color: #e11d48;
    transform: rotate(90deg);
}

.sg-promo-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff1f2;
    color: #e11d48;
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 5px 14px;
    border-radius: 999px;
    margin-bottom: 12px;
    border: 1px solid #fecdd3;
}

.sg-promo-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px;
    letter-spacing: -0.02em;
    line-height: 1.25;
}

.sg-promo-sub {
    font-size: 13.5px;
    color: #64748b;
    margin: 0 0 18px;
    line-height: 1.4;
}

.sg-promo-timer-box {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    border-radius: 14px;
    padding: 12px 16px;
    margin-bottom: 18px;
}
.sg-promo-timer-label {
    font-size: 12px;
    color: #94a3b8;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.sg-promo-countdown {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.sg-cd-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.sg-cd-unit span {
    font-size: 20px;
    font-weight: 800;
    color: #fb7185;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    padding: 2px 10px;
    min-width: 44px;
}
.sg-cd-unit small {
    font-size: 10px;
    color: #cbd5e1;
    margin-top: 3px;
    text-transform: uppercase;
}
.sg-cd-sep {
    font-size: 18px;
    font-weight: 800;
    color: #fb7185;
    margin-top: -12px;
}

.sg-promo-voucher-card {
    background: #fff7ed;
    border: 1.5px dashed #f97316;
    border-radius: 14px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
    text-align: left;
}
.sg-voucher-code {
    font-size: 17px;
    font-weight: 800;
    color: #c2410c;
    letter-spacing: 0.08em;
}
.sg-voucher-desc {
    font-size: 12px;
    color: #7c2d12;
    margin-top: 2px;
}
.sg-btn-copy {
    background: #ea580c;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.sg-btn-copy:hover {
    background: #c2410c;
    transform: translateY(-1px);
}

.sg-promo-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.sg-promo-btn-shop {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    color: #ffffff;
    text-decoration: none;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 14.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(225, 29, 72, 0.35);
    transition: all 0.2s ease;
}
.sg-promo-btn-shop:hover {
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(225, 29, 72, 0.45);
}
.sg-promo-btn-skip {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    padding: 6px;
    transition: color 0.2s ease;
}
.sg-promo-btn-skip:hover {
    color: #475569;
}

.sg-floating-promo-btn {
    position: fixed;
    bottom: 24px;
    left: 24px;
    background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%);
    color: #ffffff;
    border: none;
    border-radius: 999px;
    padding: 10px 18px 10px 14px;
    box-shadow: 0 8px 24px rgba(225, 29, 72, 0.4);
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    z-index: 999998;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    animation: sgPulseFloating 2.5s infinite;
}
.sg-floating-promo-btn:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 12px 30px rgba(225, 29, 72, 0.5);
}
.sg-floating-icon {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

@keyframes sgFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes sgPopUp {
    from { opacity: 0; transform: scale(0.92) translateY(12px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes sgPulseFloating {
    0%, 100% { box-shadow: 0 8px 24px rgba(225, 29, 72, 0.35); }
    50% { box-shadow: 0 8px 30px rgba(225, 29, 72, 0.65); transform: translateY(-1px); }
}

@media (max-width: 576px) {
    .sg-promo-modal {
        padding: 24px 18px 20px;
        border-radius: 20px;
    }
    .sg-promo-title {
        font-size: 19px;
    }
    .sg-floating-text {
        display: none;
    }
    .sg-floating-promo-btn {
        padding: 12px;
        border-radius: 50%;
        bottom: 20px;
        left: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const promoOverlay = document.getElementById('sgPromoOverlay');
    const btnClose = document.getElementById('btnPromoClose');
    const btnSkip = document.getElementById('btnPromoSkip');
    const btnShop = document.getElementById('btnPromoShop');
    const btnFloating = document.getElementById('btnOpenPromoFloating');
    const btnCopy = document.getElementById('btnCopyPromoCode');

    function closePromo() {
        if (promoOverlay) {
            promoOverlay.style.display = 'none';
            try {
                sessionStorage.setItem('sg_promo_closed', '1');
            } catch (e) {}
        }
    }

    function openPromo() {
        if (promoOverlay) {
            promoOverlay.style.display = 'flex';
        }
    }

    if (btnClose) btnClose.addEventListener('click', closePromo);
    if (btnSkip) btnSkip.addEventListener('click', closePromo);
    if (btnShop) btnShop.addEventListener('click', function () {
        closePromo();
    });

    if (promoOverlay) {
        promoOverlay.addEventListener('click', function (e) {
            if (e.target === promoOverlay) {
                closePromo();
            }
        });
    }

    if (btnFloating) {
        btnFloating.addEventListener('click', openPromo);
    }

    if (btnCopy) {
        btnCopy.addEventListener('click', function () {
            const code = document.getElementById('promoCodeText')?.innerText || 'HOATUOI2026';
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(() => {
                    btnCopy.innerHTML = '<i class="fa-solid fa-check"></i> Đã chép!';
                    btnCopy.style.background = '#16a34a';
                    setTimeout(() => {
                        btnCopy.innerHTML = '<i class="fa-regular fa-copy"></i> Sao chép';
                        btnCopy.style.background = '#ea580c';
                    }, 2000);
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = code;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                btnCopy.innerHTML = '<i class="fa-solid fa-check"></i> Đã chép!';
                btnCopy.style.background = '#16a34a';
                setTimeout(() => {
                    btnCopy.innerHTML = '<i class="fa-regular fa-copy"></i> Sao chép';
                    btnCopy.style.background = '#ea580c';
                }, 2000);
            }
        });
    }

    const hasClosed = sessionStorage.getItem('sg_promo_closed');
    if (!hasClosed) {
        setTimeout(function () {
            openPromo();
        }, 1200);
    }

    function updateCountdown() {
        const now = new Date();
        const endOfDay = new Date();
        endOfDay.setHours(23, 59, 59, 999);
        
        let diff = endOfDay - now;
        if (diff < 0) diff = 0;

        const h = Math.floor(diff / (1000 * 60 * 60));
        const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const s = Math.floor((diff % (1000 * 60)) / 1000);

        const elH = document.getElementById('cdHours');
        const elM = document.getElementById('cdMins');
        const elS = document.getElementById('cdSecs');

        if (elH) elH.innerText = String(h).padStart(2, '0');
        if (elM) elM.innerText = String(m).padStart(2, '0');
        if (elS) elS.innerText = String(s).padStart(2, '0');
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
});
</script>
