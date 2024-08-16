<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    {{-- JQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <title>Leads TPFx</title>
</head>

<body>
    <a href="/leads" class="btn btn-danger float-start ms-5">Back</a>
    <div class="d-flex justify-content-center mt-5">
        <h2 class="text-center">Upload Leads</h2>
    </div>
    <hr class="mx-auto col-5">
    <div class="d-flex flex-column align-items-center justify-content-center">
        @if (session('success'))
            <div class="alert alert-info alert-dismissible fade show col-4" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <form id="form-upload" class="col-4" method="post" action="{{ route('leads.upload_leads') }}"
            enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="formFile" class="form-label">File Leads</label>
                <input class="form-control" type="file" id="file_leads" name="file_leads" required
                    accept=".csv, text/csv, .xlsx, text/xlsx">
            </div>
            <div class="mb-2">
                <select id="leads" class="form-select select2" name="leads">
                    <option value="" disabled selected>Open this select menu</option>
                    @foreach ($spv as $item)
                        <option value="{{ $item->staffid }}">{{ $item->firstname . ' ' . $item->lastname }}</option>
                    @endforeach
                </select>
            </div>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th class="text-white">Act</th>
                        <th class="text-white">SPV</th>
                        <th class="text-white">Persentase</th>
                    </tr>
                </thead>
                <tbody id="spv-table-body"></tbody>
            </table>
            <button id="submit-btn" type="button" class="btn btn-outline-primary col-12">
                Upload
            </button>
        </form>
    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
            const rowTemplate = (data) => {
                return `
                    <tr id="row-spv-${data.id}">
                        <td><button data-id="${data.id}" class="btn btn-sm btn-danger delete-spv">x</button></td>
                        <td><input name="spv_id[]" type="hidden" value="${data.id}">${data.name}</td>
                        <td><input step="1" style="width: 100% !important;" name="persentase[]" type="number" min="0"></td>
                    </tr>
                `
            }

            $('#leads').change(function() {
                var selectedOption = $(this).find('option:selected');
                const name = selectedOption.text();
                const id = selectedOption.val();
                const data = {
                    id,
                    name,
                }

                if ($(`#row-spv-${data.id}`).length < 1) {
                    $('#spv-table-body').append(rowTemplate(data));
                }
            })

            $(document).on('click', '.delete-spv', function(e) {
                e.preventDefault();
                const result = confirm("Apakah anda yakin ingin menghapus SPV ini?");
                if (result) {
                    const id = $(this).data('id');
                    $(`#row-spv-${id}`).remove();
                }
            })

            function calculateTotal() {
                let total = 0;
                $('input[name="persentase[]"]').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                return total;
            }

            $('#submit-btn').click(function(e) {
                e.preventDefault()
                const persentase = calculateTotal()
                persentase == 100 ? $('#form-upload').submit() : alert('Persentase harus 100%')
            });
        });
    </script>
</body>

</html>
