<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Template</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Google Fonts (Titillium Web) -->
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body {
            font-family: 'Titillium Web', sans-serif;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .h1,
        .h2,
        .h3,
        .h4,
        .h5,
        .h6,
        p {
            margin-bottom: 0;
        }

        .btn-xs {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
    </style>

</head>

<body class="bg-light-subtle">

    <!-- Wrapper -->
    <div class="d-flex">

        <!-- Nav -->
        <nav class="d-flex flex-column vh-100 bg-white border-end border-light-subtle">

            <!-- Logo -->
            <div class="p-4 pb-0">

                <img src="https://placehold.co/38x38" alt="User Avatar" class="rounded"
                    style="width: 38px; height: 38px;">

            </div>
            <!-- Logo -->

            <!-- Menu -->
            <div class="p-4 flex-grow-1">

                <ul class="nav nav-pills flex-column text-nowrap">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">
                            <i class="fa-solid fa-timeline small me-1"></i>
                            Timeline
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-circle-user small me-1"></i>
                            Accounts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-exchange-alt small me-1"></i>
                            Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-repeat small me-1"></i>
                            Recurring
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-bullseye small me-1"></i>
                            Goals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-cog small me-1"></i>
                            Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa-solid fa-right-from-bracket small me-1"></i>
                            Sign out
                        </a>
                    </li>
                </ul>

            </div>
            <!-- Menu -->

            <!-- User -->
            <div class="p-4 pt-0">

                <div class="d-flex gap-2 align-items-center">
                    <img src="https://placehold.co/38x38" alt="User Avatar" class="rounded">
                    <p>Jane Smith</p>
                </div>

            </div>
            <!-- User -->

        </nav>
        <!-- Nav -->

        <!-- Main Content -->
        <main class="flex-grow-1">

            <!-- Container -->
            <div class="p-4">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div class="d-flex align-items-center gap-3">

                        <h1 class="fs-4 fw-semibold">Timeline</h1>

                    </div>

                    <div class="d-flex align-items-center gap-3">

                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-outline-primary">
                                <i class="fa-solid fa-angle-left small"></i>
                            </button>
                            <p>
                                Feb 2026 - Jan 2027
                            </p>
                            <button class="btn btn-outline-primary">
                                <i class="fa-solid fa-angle-right small"></i>
                            </button>
                        </div>

                        <div class="btn-group">
                            <a href="#" class="btn btn-primary active" aria-current="page">Timeline</a>
                            <a href="#" class="btn btn-primary">Calendar</a>
                        </div>

                        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#createTransaction">
                            Add
                        </button>

                    </div>

                </div>
                <!-- Header -->

                <!-- Content -->
                <div class="card border-light-subtle rounded-4 overflow-auto" style="height: calc(100vh - 110px);">

                    <!-- Row -->
                    <div class="row g-0 flex-nowrap h-100">

                        <!-- Accounts -->
                        <div class="col-lg-2 h-100">

                            <div class="p-4 d-flex flex-column h-100">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="fs-5 fw-semibold">Accounts</h2>
                                    <button class="btn btn-xs btn-primary">
                                        <i class="fa-solid fa-plus small"></i>
                                    </button>
                                </div>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>
                                            <a href="#" class="link-primary text-decoration-none">
                                                Wealthsimple
                                            </a>
                                        </p>
                                        <p>CAD $90,000</p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>
                                            <a href="#" class="link-primary text-decoration-none">
                                                Scotiabank
                                            </a>
                                        </p>
                                        <p>CAD $90,000</p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>
                                            <a href="#" class="link-primary text-decoration-none">
                                                Tangerine
                                            </a>
                                        </p>
                                        <p>CAD $90,000</p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>
                                            <a href="#" class="link-primary text-decoration-none">
                                                Manulife
                                            </a>
                                        </p>
                                        <p>CAD $90,000</p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>
                                            <a href="#" class="link-primary text-decoration-none">
                                                Cash
                                            </a>
                                        </p>
                                        <p>USD $1,800</p>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-between align-items-end">
                                    <p>Net Worth</p>
                                    <p class="fs-5 fw-semibold text-success">CAD $253,000</p>
                                </div>

                            </div>

                        </div>
                        <!-- Accounts -->

                        <!-- Feb 2026 -->
                        <div class="col-lg-2 h-100">

                            <div class="border-start border-light-subtle p-4 d-flex flex-column h-100">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="fs-5 fw-semibold">Feb 2026</h2>
                                    <button class="btn btn-xs btn-primary disabled opacity-0">
                                        <i class="fa-solid fa-plus small"></i>
                                    </button>
                                </div>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rent</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $2,400.00
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Amex Credit Card</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $1,974.90
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rafa EI</p>
                                        <p>
                                            <a href="#" class="link-success text-decoration-none">
                                                CAD $1,232.00
                                            </a>
                                        </p>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-between align-items-end">
                                    <p>End Balance</p>
                                    <p class="fs-5 fw-semibold text-success">CAD $253,000</p>
                                </div>

                            </div>

                        </div>
                        <!-- Feb 2026 -->

                        <!-- Mar 2026 -->
                        <div class="col-lg-2 h-100">

                            <div class="border-start border-light-subtle p-4 d-flex flex-column h-100">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="fs-5 fw-semibold">Mar 2026</h2>
                                    <button class="btn btn-xs btn-primary disabled opacity-0">
                                        <i class="fa-solid fa-plus small"></i>
                                    </button>
                                </div>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rent</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $2,400.00
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Amex Credit Card</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $1,974.90
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rafa EI</p>
                                        <p>
                                            <a href="#" class="link-success text-decoration-none">
                                                CAD $1,232.00
                                            </a>
                                        </p>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-between align-items-end">
                                    <p>End Balance</p>
                                    <p class="fs-5 fw-semibold text-success">CAD $253,000</p>
                                </div>

                            </div>

                        </div>
                        <!-- Mar 2026 -->

                        <!-- Apr 2026 -->
                        <div class="col-lg-2 h-100">

                            <div class="border-start border-light-subtle p-4 d-flex flex-column h-100">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="fs-5 fw-semibold">Apr 2026</h2>
                                    <button class="btn btn-xs btn-primary disabled opacity-0">
                                        <i class="fa-solid fa-plus small"></i>
                                    </button>
                                </div>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rent</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $2,400.00
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Amex Credit Card</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $1,974.90
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rafa EI</p>
                                        <p>
                                            <a href="#" class="link-success text-decoration-none">
                                                CAD $1,232.00
                                            </a>
                                        </p>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-between align-items-end">
                                    <p>End Balance</p>
                                    <p class="fs-5 fw-semibold text-success">CAD $253,000</p>
                                </div>

                            </div>

                        </div>
                        <!-- Apr 2026 -->

                        <!-- May 2026 -->
                        <div class="col-lg-2 h-100">

                            <div class="border-start border-light-subtle p-4 d-flex flex-column h-100">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="fs-5 fw-semibold">May 2026</h2>
                                    <button class="btn btn-xs btn-primary disabled opacity-0">
                                        <i class="fa-solid fa-plus small"></i>
                                    </button>
                                </div>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rent</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $2,400.00
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Amex Credit Card</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $1,974.90
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rafa EI</p>
                                        <p>
                                            <a href="#" class="link-success text-decoration-none">
                                                CAD $1,232.00
                                            </a>
                                        </p>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-between align-items-end">
                                    <p>End Balance</p>
                                    <p class="fs-5 fw-semibold text-success">CAD $253,000</p>
                                </div>

                            </div>

                        </div>
                        <!-- May 2026 -->

                        <!-- Jun 2026 -->
                        <div class="col-lg-2 h-100">

                            <div class="border-start border-light-subtle p-4 d-flex flex-column h-100">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="fs-5 fw-semibold">Jun 2026</h2>
                                    <button class="btn btn-xs btn-primary disabled opacity-0">
                                        <i class="fa-solid fa-plus small"></i>
                                    </button>
                                </div>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rent</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $2,400.00
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Amex Credit Card</p>
                                        <p>
                                            <a href="#" class="link-danger text-decoration-none">
                                                -CAD $1,974.90
                                            </a>
                                        </p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <p>Rafa EI</p>
                                        <p>
                                            <a href="#" class="link-success text-decoration-none">
                                                CAD $1,232.00
                                            </a>
                                        </p>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-between align-items-end">
                                    <p>End Balance</p>
                                    <p class="fs-5 fw-semibold text-success">CAD $253,000</p>
                                </div>

                            </div>

                        </div>
                        <!-- Jun 2026 -->

                    </div>
                    <!-- Row -->

                </div>
                <!-- Content -->

            </div>
            <!-- Container -->

        </main>
        <!-- Main Content -->

    </div>
    <!-- Wrapper -->

    <!-- Offcanvas: Create Transaction -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="createTransaction" aria-labelledby="createTransactionLabel">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom border-light-subtle p-4">

            <h2 class="fs-5 fw-semibold">Add Transaction</h2>
            <button class="btn btn-xs btn-primary" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="fa-solid fa-xmark small"></i>
            </button>

        </div>
        <!-- Header -->

        <!-- Content -->
        <div class="p-4 flex-grow-1">

            <input type="hidden" name="action" id="txnAction" value="create">
            <input type="hidden" name="id" id="txnId" value="">
            <input type="hidden" name="redirect"  value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cashtimeline/public/dashboard') ?>">

            <div class="mb-3">
                <label for="type" class="form-label">
                    <span class="text-danger">*</span> Type
                </label>
                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                    <input type="radio" class="btn-check" name="type" id="type1" autocomplete="off" checked>
                    <label class="btn btn-outline-primary" for="type1">Income</label>

                    <input type="radio" class="btn-check" name="type" id="type2" autocomplete="off">
                    <label class="btn btn-outline-primary" for="type2">Expense</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="transaction_date" class="form-label">
                    <span class="text-danger">*</span> Date
                </label>
                <input type="date" name="transaction_date" id="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

        </div>
        <!-- Content -->

        <!-- Buttons -->
        <div class="d-flex gap-3 border-top border-light-subtle p-4">

            <button class="btn btn-secondary" data-bs-dismiss="offcanvas" aria-label="Close">
                Cancel
            </button>

            <button class="btn btn-primary w-100">
                Submit
            </button>

        </div>
        <!-- Buttons -->

    </div>
    <!-- Offcanvas: Create Transaction -->

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

</body>

</html>