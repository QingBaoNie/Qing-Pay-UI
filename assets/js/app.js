// 支付相关变量
let selectedPayment = "wxpay";
let checkInterval = null;

// DOM 加载完成后初始化
document.addEventListener('DOMContentLoaded', function() {
    // 判断当前页面
    if (window.location.pathname.includes('success.php')) {

    } else {
        // 支付页面逻辑
        document.querySelector('.fade-in').classList.add('active');
        
        // 支付方式切换事件
        const wxpayBtn = document.getElementById('wxpay-btn');
        const alipayBtn = document.getElementById('alipay-btn');
        
        wxpayBtn.addEventListener('click', function() {
            selectedPayment = 'wxpay';
            wxpayBtn.classList.add('selected');
            alipayBtn.classList.remove('selected');
        });
        
        alipayBtn.addEventListener('click', function() {
            selectedPayment = 'alipay';
            alipayBtn.classList.add('selected');
            wxpayBtn.classList.remove('selected');
        });

        // 金额选择事件
        const amountSelect = document.getElementById('amount-select');
        if (amountSelect) {
            amountSelect.addEventListener('change', function() {
                const customAmount = document.getElementById('custom-amount');
                if (this.value === 'custom') {
                    customAmount.classList.remove('hidden');
                    this.classList.add('hidden');
                } else {
                    customAmount.classList.add('hidden');
                    this.classList.remove('hidden');
                }
            });
        }

        // 提交按钮事件
        const submitButton = document.getElementById('submit');
        if (submitButton) {
            // 重置按钮状态的函数
            const resetButton = () => {
                submitButton.disabled = false;
                submitButton.innerText = "前往付款";
                // 重新绑定创建订单事件
                submitButton.onclick = createOrder;
            };

            // 创建订单的函数
            const createOrder = () => {
                const amountSelect = document.getElementById('amount-select');
                const customAmount = document.getElementById('custom-amount').value;
                const qrcodeContainer = document.getElementById('qrcode-container');
                const qrcodeDiv = document.getElementById('qrcode');
                const qrLoader = document.getElementById('qr-loader');
                const orderInfo = document.getElementById('order-info');

                let amount = amountSelect.value === 'custom' ? customAmount : amountSelect.value;

                if (!amount || parseFloat(amount) <= 0) {
                    alert("❌ 请输入有效的支付金额");
                    return;
                }

                // 禁用按钮并显示加载状态
                submitButton.disabled = true;
                submitButton.innerText = "正在创建订单...";

                qrcodeContainer.classList.remove("hidden");
                qrcodeDiv.classList.add("hidden");
                qrLoader.style.display = "block";

                fetch('/pay/create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `money=${amount}&type=${selectedPayment}`
                })
                .then(response => response.text())
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Response text:', text);
                        throw new Error(`JSON解析失败: ${e.message}`);
                    }
                    
                    if (data.code === 1) {
                        // 订单创建成功
                        const payUrl = data.payurl || data.qrcode;
                        const order_id = data.order_id; 
                        
                        orderInfo.innerHTML = `📜 订单号: ${order_id} | 💰 金额: ¥${amount}`;
                        
                        // 更改按钮状态
                        submitButton.disabled = false;
                        submitButton.innerText = "无法扫码? 🔗 点这里";
                        submitButton.onclick = () => {
                            if (payUrl) {
                                window.location.href = payUrl;
                            } else {
                                alert("支付链接未获取到，请重试");
                                resetButton();
                            }
                        };

                        // 显示二维码
                        setTimeout(() => {
                            qrLoader.style.display = "none";
                            qrcodeDiv.classList.remove("hidden");
                            qrcodeDiv.innerHTML = "";
                            new QRCode(qrcodeDiv, {
                                text: payUrl,
                                width: 160,
                                height: 160
                            });

                            // 开始检查订单状态（使用正确的订单号字段）
                            if (order_id) {
                                checkInterval = setInterval(() => checkOrderStatus(order_id), 3000);
                                console.log("开始检查订单状态:", order_id); // 添加日志便于调试
                            } else {
                                console.error("未获取到订单号，无法开始检查状态");
                            }
                        }, 1000);
                    } else {
                        throw new Error(data.msg || "创建订单失败");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(`请求失败: ${error.message}`);
                    qrcodeContainer.classList.add("hidden");
                    resetButton();
                });
            };

            // 初始绑定创建订单事件
            submitButton.onclick = createOrder;
        }
    }
});

function checkOrderStatus(orderId) {
    fetch(`pay/query.php?order_id=${orderId}`)
        .then(response => response.text())
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Response text:', text);
                throw new Error(`JSON解析失败: ${e.message}`);
            }
            
            // 修改状态判断逻辑
            if (data.data && data.data.trade_status === "PAID") {
                clearInterval(checkInterval);
                
                // 创建表单并提交到success.php
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/success.php';

                // 创建隐藏字段
                const fields = {
                    'order_id': orderId,
                    'money': data.data.money,
                    'type': data.data.type,
                    'pay_time': data.data.pay_time
                };

                // 添加表单字段
                Object.entries(fields).forEach(([key, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                });

                // 添加到文档并提交
                document.body.appendChild(form);
                form.submit();
            }
        })
        .catch(error => {
            console.error('订单状态检查失败:', error);
        });
}
