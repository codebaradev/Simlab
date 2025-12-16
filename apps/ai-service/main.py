from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List
import joblib
import pandas as pd
import numpy as np
import os

# --- 1. INISIALISASI & LOAD MODEL ---
app = FastAPI(title="API Rekomendasi Lab Kampus")

# Pastikan file .pkl ada di folder yang sama dengan main.py
if not os.path.exists("model_rekomendasi.pkl") or not os.path.exists("scaler.pkl"):
    print("⚠️ PERINGATAN: File model (.pkl) tidak ditemukan! Pastikan sudah digenerate.")
else:
    try:
        model = joblib.load('model_rekomendasi.pkl')
        scaler = joblib.load('scaler.pkl')
        print("✅ Model & Scaler berhasil dimuat.")
    except Exception as e:
        print(f"❌ Error memuat model: {e}")

# --- 2. DEFINISI SCHEMA DATA (Agar data yang masuk valid) ---

class MatkulItem(BaseModel):
    course_name: str
    num_students: int       
    ram_required_gb: float
    cpu_cores_required: int
    gpu_required: str       # "yes" atau "no"
    required_software: str  # Contoh: "Android Studio, Flutter"
    difficulty_level: str   # "low", "medium", "high"

class RoomItem(BaseModel):
    room_id: str
    room_name: str
    capacity: int
    ram_available_gb: float
    cpu_cores_available: int
    gpu_available: str      # "yes" atau "no"
    os_type: str            # "Windows 11", "Ubuntu 22.04"

class PredictionRequest(BaseModel):
    matkul: MatkulItem
    ruangan: List[RoomItem]

# --- 3. FUNGSI LOGIKA PENDUKUNG (Helper Functions) ---

def cek_kecocokan_software(software_dibutuhkan: str, os_type: str) -> float:
    """Menghitung persentase software yang tersedia di OS ruangan"""
    if not software_dibutuhkan or str(software_dibutuhkan).lower() == "nan":
        return 1.0

    # Database Software (Sesuaikan dengan kebutuhan kampus)
    os_software_support = {
        'Windows 11': ['Android Studio', 'VS Code', 'Flutter', 'Node.js', 'XAMPP', 'Wireshark', 'MySQL Workbench', 'PyCharm', 'Cisco Packet Tracer'],
        'Windows 10': ['Android Studio', 'VS Code', 'Flutter', 'Node.js', 'XAMPP', 'Wireshark', 'MySQL Workbench', 'PyCharm', 'Cisco Packet Tracer'],
        'Ubuntu 20.04': ['VirtualBox', 'VMware Workstation', 'TensorFlow', 'PyTorch', 'Jupyter', 'OpenCV', 'Docker'],
        'Ubuntu 22.04': ['VirtualBox', 'VMware Workstation', 'TensorFlow', 'PyTorch', 'Jupyter', 'OpenCV', 'Docker']
    }

    # Ubah string input menjadi set (kumpulan unik)
    dibutuhkan = set(s.strip() for s in str(software_dibutuhkan).split(','))
    terpasang = set(os_software_support.get(os_type, []))
    
    if not dibutuhkan: return 1.0
    
    # Hitung irisan (intersection)
    cocok = len(dibutuhkan.intersection(terpasang))
    return round(cocok / len(dibutuhkan), 2)

def siapkan_fitur(rasio_kap, r_ram, r_cpu, gpu, soft, diff):
    """
    Feature Engineering:
    Menggunakan max(0, val) agar kekurangan (defisit) dianggap 0, bukan negatif linear.
    Harus SAMA PERSIS dengan saat training model.
    """
    return [[
        rasio_kap,
        max(0, r_ram),  # Rectified (Nilai negatif jadi 0)
        max(0, r_cpu),  # Rectified
        gpu,
        soft,
        diff
    ]]

# --- 4. ENDPOINT API (Bagian yang dipanggil Web Teman) ---

@app.get("/")
def home():
    return {
        "message": "API Sistem Rekomendasi Lab Aktif", 
        "docs": "Buka /docs untuk dokumentasi"
    }

@app.post("/predict")
def predict_room(data: PredictionRequest):
    """
    Menerima JSON berisi data matkul dan list ruangan,
    Mengembalikan JSON berisi list ruangan yang sudah diranking beserta skornya.
    """
    hasil_rekomendasi = []
    kesulitan_map = {'low': 0, 'medium': 1, 'high': 2}
    
    matkul = data.matkul
    
    # Loop semua ruangan yang dikirim dari web
    for ruangan in data.ruangan:

        print(ruangan)
        print(matkul)
        
        # [FILTER 1] Hard Constraint: Kapasitas
        # Jika kapasitas ruangan kurang dari mahasiswa, langsung skip.
        if ruangan.capacity < matkul.num_students:
            continue
            
        # [HITUNG FITUR DASAR]
        rasio_kapasitas = min(1.0, matkul.num_students / ruangan.capacity)
        
        # Hitung surplus/defisit spek (bisa negatif)
        rasio_ram = (ruangan.ram_available_gb - matkul.ram_required_gb) / matkul.ram_required_gb
        rasio_cpu = (ruangan.cpu_cores_available - matkul.cpu_cores_required) / matkul.cpu_cores_required
        
        # Cek GPU (1 jika butuh & ada, 0 jika lainnya)
        butuh_gpu = 1 if matkul.gpu_required.lower() == 'yes' else 0
        punya_gpu = 1 if ruangan.gpu_available.lower() == 'yes' else 0
        skor_gpu = 1 if (butuh_gpu and punya_gpu) else 0
        
        # Cek Software
        rasio_software = cek_kecocokan_software(matkul.required_software, ruangan.os_type)
        
        # Map Difficulty Level
        level_kesulitan = kesulitan_map.get(matkul.difficulty_level.lower(), 1) # Default medium jika typo
        
        # [SIAPKAN FITUR UNTUK MODEL]
        # Panggil fungsi helper agar logika sama dengan training
        input_fitur = siapkan_fitur(
            rasio_kapasitas, rasio_ram, rasio_cpu, skor_gpu, rasio_software, level_kesulitan
        )
        
        # [PREDIKSI MENGGUNAKAN MODEL]
        # 1. Scale data dulu
        input_scaled = scaler.transform(input_fitur)
        # 2. Prediksi skor
        skor_final = model.predict(input_scaled)[0]
        
        # [SIMPAN HASIL]
        hasil_rekomendasi.append({
            "room_id": ruangan.room_id,
            "room_name": ruangan.room_name,
            "score": float(np.clip(skor_final, 0, 1)), # Pastikan range 0-1
            "details": {
                "match_software": f"{int(rasio_software*100)}%",
                "status_ram": "Cukup" if rasio_ram >= 0 else "Kurang",
                "status_gpu": "Cocok" if skor_gpu or not butuh_gpu else "Tidak Ada"
            }
        })
    
    # Urutkan list berdasarkan score tertinggi (Ranking)
    hasil_rekomendasi.sort(key=lambda x: x['score'], reverse=True)
    
    return {
        "status": "success",
        "total_candidates": len(data.ruangan),
        "filtered_candidates": len(hasil_rekomendasi),
        "recommendations": hasil_rekomendasi
    }
