<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список лидов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .card {
            border: none;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(8px);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .btn-primary {
            background: linear-gradient(45deg, #647dee, #8b5cf6);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #5a6ed9, #7c4edf);
            transform: scale(1.03);
        }

        .btn-outline-primary {
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            color: #647dee;
            border-color: #647dee;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: rgba(100, 125, 238, 0.1);
            transform: scale(1.03);
            color: #647dee;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #647dee;
            box-shadow: 0 0 0 3px rgba(100, 125, 238, 0.15);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(45deg, #647dee, #8b5cf6);
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: rgba(100, 125, 238, 0.03);
            transform: translateX(3px);
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            background: linear-gradient(45deg, #93c5fd, #a5b4fc);
            color: #1e3a8a;
        }

        .pagination .page-link {
            border-radius: 10px;
            margin: 0 5px;
            padding: 0.75rem 1.5rem;
            color: #647dee;
            border-color: #e5e7eb;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: #647dee;
            color: white;
            transform: scale(1.05);
        }

        .alert {
            border-radius: 10px;
            border: none;
            background: rgba(248, 113, 113, 0.1);
            color: #b91c1c;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="card shadow-sm mb-5">
        <div class="card-body p-4">
            <h3 class="card-title mb-4 fw-semibold text-dark">Фильтры лидов</h3>
            <form method="GET" class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-medium text-muted">С даты</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-medium text-muted">По дату</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-medium text-muted">Сортировка</label>
                    <select name="sort" class="form-select">
                        <option value="asc" {{ request('sort') === 'asc' ? 'selected' : '' }}>Сначала старые</option>
                        <option value="desc" {{ request('sort') === 'desc' ? 'selected' : '' }}>Сначала новые</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label fw-medium text-muted">На странице</label>
                    <select name="limit" class="form-select">
                        @foreach ([10, 25, 50] as $option)
                            <option value="{{ $option }}" {{ request('limit') == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-3 mt-3">
                    <button type="submit" class="btn btn-primary">Применить</button>
                    <a href="{{ url()->current() }}" class="btn btn-outline-primary">Сбросить</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th scope="col">Имя</th>
                        <th scope="col">Статус</th>
                        <th scope="col">Контакт</th>
                        <th scope="col">Обновлено</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($leads as $lead)
                        <tr>
                            <td>{{ $lead['name'] ?? '-' }}</td>
                            <td>
                                <span class="badge">
                                    {{ $lead['status_name'] }}
                                </span>
                            </td>
                            <td>{{ $lead['_embedded']['contacts'][0]['name'] ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($lead['updated_at'])->toDateTimeString() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="alert text-center my-3" role="alert">
                                    <i class="bi bi-exclamation-circle fs-4 me-2"></i>
                                    <span class="fs-5">Нет лидов, соответствующих вашим критериям. <a href="https://{{ env('AMO_BASE_DOMAIN') }}/leads/pipeline/?skip_filter=Y" target="_blank">Создайте сделку в AMO CRM</a> для отображения здесь.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        @php $prev = $page > 1 ? $page - 1 : 1; $next = $page + 1; @endphp
                        <li class="page-item">
                            <a class="page-link" href="?{{ http_build_query(array_merge(request()->all(), ['page' => $prev])) }}">
                                <i class="bi bi-chevron-left"></i> Назад
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?{{ http_build_query(array_merge(request()->all(), ['page' => $next])) }}">
                                Вперед <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
