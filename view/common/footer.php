    </main>
        <!-- 页脚 -->
        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0">© 2025 MVC留言板 | 基于 Bootstrap 5 构建</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0">
                            <i class="fa fa-code me-1"></i>PHP + PDO + MySQL + MVC
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Bootstrap 5 JS + Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- 自定义脚本 -->
        <script>
            // 验证码刷新
            document.addEventListener('DOMContentLoaded', function() {
               
                // 表单提交前验证（非空）
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        const requiredInputs = this.querySelectorAll('[required]');
                        let isValid = true;
                        
                        requiredInputs.forEach(input => {
                            if (!input.value.trim()) {
                                isValid = false;
                                // 添加 Bootstrap 错误样式
                                input.classList.add('is-invalid');
                                // 显示错误提示
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = '此字段不能为空';
                                if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
                                    input.parentNode.appendChild(feedback);
                                }
                            } else {
                                input.classList.remove('is-invalid');
                                const feedback = input.parentNode.querySelector('.invalid-feedback');
                                if (feedback) feedback.remove();
                            }
                        });

                        if (!isValid) e.preventDefault();
                    });

                    // 输入框变化时移除错误样式
                    const inputs = form.querySelectorAll('input, textarea');
                    inputs.forEach(input => {
                        input.addEventListener('input', function() {
                            this.classList.remove('is-invalid');
                            const feedback = this.parentNode.querySelector('.invalid-feedback');
                            if (feedback) feedback.remove();
                        });
                    });
                });
            });
        </script>
    </body>
</html>