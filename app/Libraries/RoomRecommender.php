<?php

namespace App\Libraries;

/**
 * RoomRecommender — Sistem Pendukung Keputusan (SPK) rekomendasi ruangan
 * menggunakan metode Simple Additive Weighting (SAW).
 *
 * KRITERIA:
 *   C1 - Selisih Kapasitas    (cost)    : selisih antara kapasitas ruangan dan jumlah peserta.
 *                                         Semakin kecil selisihnya, semakin baik (tidak boros ruang
 *                                         besar untuk kegiatan kecil).
 *   C2 - Kecocokan Fasilitas  (benefit) : jumlah fasilitas yang diminta user dan tersedia di ruangan.
 *                                         Semakin banyak yang cocok, semakin baik.
 *   C3 - Tingkat Keterpakaian (cost)    : jumlah booking ruangan tsb dalam 30 hari terakhir.
 *                                         Semakin jarang dipakai, semakin baik (pemakaian merata,
 *                                         peluang bentrok/konflik jadwal ke depan lebih kecil).
 *
 * LANGKAH SAW:
 *   1. Susun matriks keputusan (nilai C1, C2, C3 tiap ruangan kandidat)
 *   2. Normalisasi:
 *        - Kriteria benefit : r = nilai / nilai_maksimum
 *        - Kriteria cost    : r = nilai_minimum / nilai
 *   3. Hitung nilai preferensi V = Σ (bobot_j × r_j)
 *   4. Ranking — nilai V terbesar = rekomendasi terbaik
 *
 * Catatan implementasi: C1 dan C3 digeser +1 sebelum dinormalisasi untuk menghindari
 * pembagian dengan nol saat nilai minimumnya 0 (ruangan pas kapasitas / belum pernah dipakai).
 * Pergeseran ini tidak mengubah urutan relatif antar ruangan.
 */
class RoomRecommender
{
    protected array $weights;

    /**
     * @param array $weights Bobot tiap kriteria, mis. ['kapasitas'=>0.4,'fasilitas'=>0.4,'keterpakaian'=>0.2].
     *                       Total bobot idealnya = 1 (100%).
     */
    public function __construct(array $weights = [])
    {
        $default = ['kapasitas' => 0.4, 'fasilitas' => 0.4, 'keterpakaian' => 0.2];
        $this->weights = array_merge($default, $weights);
    }

    public function getWeights(): array
    {
        return $this->weights;
    }

    /**
     * @param array $rooms           Ruangan kandidat yang SUDAH lolos filter keras (kapasitas cukup,
     *                                status tersedia, tidak bentrok jadwal). Tiap elemen minimal:
     *                                id, nama_ruangan, kode_ruangan, kapasitas, fasilitas (string),
     *                                jumlah_booking (int, dihitung di controller).
     * @param int   $jumlahPeserta
     * @param array $fasilitasDiminta Daftar nama fasilitas yang dibutuhkan user.
     * @return array Ruangan terurut dari skor (V) tertinggi ke terendah, tiap elemen berisi
     *               data ruangan asli + breakdown nilai kriteria (c1..c3, r1..r3, v) untuk
     *               ditampilkan sebagai tabel perhitungan yang transparan.
     */
    public function rank(array $rooms, int $jumlahPeserta, array $fasilitasDiminta): array
    {
        if (empty($rooms)) {
            return [];
        }

        $rows = [];
        foreach ($rooms as $r) {
            $selisih = max((int) $r['kapasitas'] - $jumlahPeserta, 0);

            $fasilitasRoom = array_filter(array_map('trim', explode(',', mb_strtolower((string) ($r['fasilitas'] ?? '')))));
            $cocok = 0;
            foreach ($fasilitasDiminta as $f) {
                $f = mb_strtolower(trim((string) $f));
                if ($f === '') {
                    continue;
                }
                foreach ($fasilitasRoom as $fr) {
                    if (str_contains($fr, $f)) {
                        $cocok++;
                        break;
                    }
                }
            }

            $rows[] = [
                'room' => $r,
                'c1_selisih_kapasitas' => $selisih,
                'c2_fasilitas_cocok'   => $cocok,
                'c3_keterpakaian'      => (int) ($r['jumlah_booking'] ?? 0),
                'c1' => $selisih + 1,
                'c2' => $cocok,
                'c3' => (int) ($r['jumlah_booking'] ?? 0) + 1,
            ];
        }

        $minC1 = min(array_column($rows, 'c1'));
        $maxC2 = max(array_column($rows, 'c2'));
        $minC3 = min(array_column($rows, 'c3'));

        foreach ($rows as &$row) {
            $row['r1'] = $minC1 / $row['c1'];                   // cost
            $row['r2'] = $maxC2 > 0 ? $row['c2'] / $maxC2 : 1;  // benefit
            $row['r3'] = $minC3 / $row['c3'];                   // cost

            $row['v'] = ($this->weights['kapasitas']   * $row['r1'])
                      + ($this->weights['fasilitas']    * $row['r2'])
                      + ($this->weights['keterpakaian'] * $row['r3']);
        }
        unset($row);

        usort($rows, fn ($a, $b) => $b['v'] <=> $a['v']);

        return $rows;
    }
}
