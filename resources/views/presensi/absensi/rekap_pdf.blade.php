<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rekapitulasi Absensi</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #000; padding: 6px; text-align: center; vertical-align: middle; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h3 { margin: 0; font-size: 14px; }
        .header p { margin: 5px 0; font-size: 12px; }
        .info { margin-bottom: 10px; font-size: 11px; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h3>Laporan Rekapitulasi Absensi</h3>
        <p>Periode: {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</p>
    </div>

    <div class="info">
        <strong>Divisi : {{ $divisiName }}</strong>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">No.</th>
                <th colspan="4">Data Karyawan</th>
                <th colspan="5">Keterangan</th>
                <th rowspan="2" style="width: 10%;">Persentase Kehadiran</th>
            </tr>
            <tr>
                <th style="width: 15%;">Nama</th>
                <th style="width: 12%;">Divisi</th>
                <th style="width: 12%;">Posisi</th>
                <th style="width: 8%;">Gender</th>
                <th style="width: 5%;">Hadir</th>
                <th style="width: 5%;">Izin</th>
                <th style="width: 5%;">Cuti</th>
                <th style="width: 5%;">Sakit</th>
                <th style="width: 5%;">Alpa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapData as $index => $data)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $data['employee']->emp_Name }}</td>
                    <td>{{ optional($data['employee']->divisi)->Div_Name ?? '-' }}</td>
                    <td>{{ optional($data['employee']->posisi)->Pos_Name ?? '-' }}</td>
                    <td>
                        @if($data['employee']->emp_Sex == 'M')
                            Laki-laki
                        @elseif($data['employee']->emp_Sex == 'F')
                            Perempuan
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $data['summary']['Hadir'] }}</td>
                    <td>{{ $data['summary']['Izin'] }}</td>
                    <td>{{ $data['summary']['Cuti'] }}</td>
                    <td>{{ $data['summary']['Sakit'] }}</td>
                    <td>{{ $data['summary']['Alpa'] }}</td>
                    <td>{{ $data['attendance_percentage'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">Tidak ada data untuk ditampilkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
