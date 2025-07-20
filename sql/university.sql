INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 1, 'Universiti Malaya', 'Lembah Pantai Kuala Lumpur Wilayah Persekutuan Kuala Lumpur', t.type_id, '50603', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 2, 'Universiti Kebangsaan Malaysia', '43600 UKM Bangi Selangor Darul Ehsan', t.type_id, '43600', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 3, 'Universiti Putra Malaysia', '43400 UPM Serdang Selangor Darul Ehsan', t.type_id, '43400', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 4, 'Universiti Sains Malaysia', '11800 USM Pulau Pinang', t.type_id, '11800', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 5, 'Universiti Teknologi Malaysia', '81310 UTM Johor Bahru Johor', t.type_id, '81310', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 6, 'Universiti Teknologi MARA', '40450 Shah Alam Selangor Darul Ehsan', t.type_id, '40450', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 7, 'International Islamic University Malaysia', 'Jalan Gombak 53100 Kuala Lumpur Selangor', t.type_id, '53100', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 8, 'Universiti Utara Malaysia', '06010 UUM Sintok Kedah Darul Aman', t.type_id, '06010', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 9, 'Universiti Malaysia Sarawak', '94300 Kota Samarahan Sarawak', t.type_id, '94300', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 10, 'Universiti Pendidikan Sultan Idris', '35900 Tanjong Malim Perak Darul Ridzuan', t.type_id, '35900', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 11, 'Universiti Malaysia Sabah', 'Jalan UMS 88400 Kota Kinabalu Sabah', t.type_id, '88400', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 12, 'Universiti Malaysia Terengganu', '21030 Kuala Nerus Terengganu Darul Iman', t.type_id, '21030', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 13, 'Universiti Tun Hussein Onn Malaysia', '86400 Parit Raja Batu Pahat Johor', t.type_id, '86400', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 14, 'Universiti Teknikal Malaysia Melaka', 'Hang Tuah Jaya 76100 Durian Tunggal Melaka', t.type_id, '76100', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 15, 'Universiti Malaysia Pahang Al-Sultan Abdullah', 'Lebuhraya Tun Razak 26300 Gambang Kuantan Pahang Darul Makmur', t.type_id, '26300', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 16, 'Universiti Malaysia Perlis', '02600 Arau Perlis Indera Kayangan', t.type_id, '02600', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 17, 'Universiti Sains Islam Malaysia', 'Bandar Baru Nilai 71800 Nilai Negeri Sembilan Darul Khusus', t.type_id, '71800', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 18, 'Universiti Sultan Zainal Abidin', 'Gong Badak Campus 21300 Kuala Nerus Terengganu Darul Iman', t.type_id, '21300', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 19, 'Universiti Malaysia Kelantan', 'Locked Bag 36 Pengkalan Chepa 16100 Kota Bharu Kelantan Darul Naim', t.type_id, '16100', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 20, 'Universiti Pertahanan Nasional Malaysia', 'Kem Sungai Besi 57000 Kuala Lumpur Wilayah Persekutuan', t.type_id, '57000', 1
FROM university_type t WHERE t.type_name = 'Public';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 21, 'Taylor''s University', 'No. 1 Jalan Taylor''s 47500 Subang Jaya Selangor', t.type_id, '47500', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 22, 'Sunway University', '5 Jalan Universiti Bandar Sunway 47500 Petaling Jaya Selangor', t.type_id, '47500', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 23, 'UCSI University', 'No. 1 UCSI Heights Jalan Puncak Menara Gading Taman Connaught 56000 Cheras Federal Territory of Kuala Lumpur', t.type_id, '56000', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 24, 'Asia Pacific University of Technology & Innovation', 'Jalan Teknologi 5 Taman Teknologi Malaysia 57000 Kuala Lumpur Wilayah Persekutuan Kuala Lumpur', t.type_id, '57000', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 25, 'Universiti Teknologi PETRONAS', '32610 Seri Iskandar Perak Darul Ridzuan', t.type_id, '32610', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 26, 'Multimedia University', 'Persiaran Multimedia 63100 Cyberjaya Selangor', t.type_id, '63100', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 27, 'International Medical University', '126 Jalan Jalil Perkasa 19 Bukit Jalil 57000 Kuala Lumpur', t.type_id, '57000', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 28, 'MAHSA University', 'Jalan SP 2 Bandar Saujana Putra 42610 Jenjarom Selangor', t.type_id, '42610', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 29, 'Management and Science University', 'University Drive Off Persiaran Olahraga Section 13 40100 Shah Alam Selangor', t.type_id, '40100', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 30, 'HELP University', 'No. 15 Jalan Sri Semantan 1 Damansara Heights 50490 Kuala Lumpur', t.type_id, '50490', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 31, 'INTI International University', 'Persiaran Perdana BBN Putra Nilai 71800 Nilai Negeri Sembilan', t.type_id, '71800', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 32, 'Limkokwing University of Creative Technology', 'Inovasi 1-1 Jalan Teknokrat 1/1 63000 Cyberjaya Selangor', t.type_id, '63000', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 33, 'Universiti Tenaga Nasional', 'Jalan IKRAM-UNITEN 43000 Kajang Selangor', t.type_id, '43000', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 34, 'Open University Malaysia', 'Jalan Tun Ismail 50480 Kuala Lumpur', t.type_id, '50480', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 35, 'Universiti Tunku Abdul Rahman', 'Jalan Universiti Bandar Barat 31900 Kampar Perak', t.type_id, '31900', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 36, 'SEGi University', 'No. 9 Jalan Teknologi Taman Sains Selangor Kota Damansara PJU 5 47810 Petaling Jaya Selangor', t.type_id, '47810', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 37, 'Infrastructure University Kuala Lumpur', 'De Baron Technoplex Persiaran Ikram-Uniten 43000 Kajang Selangor', t.type_id, '43000', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 38, 'INCEIF University', 'Lorong Universiti A 59100 Kuala Lumpur Wilayah Persekutuan', t.type_id, '59100', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 39, 'DRB-HICOM University of Automotive Malaysia', 'Lot 1449 Jalan Tun Dr Ismail 26607 Pekan Pahang', t.type_id, '26607', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 40, 'Malaysia University of Science and Technology', 'Block B Encorp Strand Garden Office No. 12 Jalan PJU 5/1 Kota Damansara 47810 Petaling Jaya Selangor', t.type_id, '47810', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 41, 'Tunku Abdul Rahman University of Management and Technology', 'Jalan Genting Kelang 53300 Setapak Kuala Lumpur', t.type_id, '53300', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 42, 'University of Wollongong Malaysia', 'Utropolis Glenmarie Jalan Kontraktor U1/14 Seksyen U1 40150 Shah Alam Selangor', t.type_id, '40150', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 43, 'International University of Malaya-Wales', 'Administration Wing 1st Floor Block A City Campus Jalan Tun Ismail 50480 Kuala Lumpur', t.type_id, '50480', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 44, 'KPJ Healthcare University', 'Lot 11 Jalan Masjid Abu Bakar Nilai 71800 Negeri Sembilan', t.type_id, '71800', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 45, 'Wawasan Open University', '54 Jalan Sultan Ahmad Shah 10050 Penang', t.type_id, '10050', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 46, 'The One Academy', 'Block B4 Leisure Commerce Square No. 9 Jalan PJS 8/9 46150 Petaling Jaya Selangor', t.type_id, '46150', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 47, 'First City University College', '2 Jalan Tembaga SD 5/2 Bandar Sri Damansara 52200 Kuala Lumpur', t.type_id, '52200', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 48, 'Monash University Malaysia', '2 Jalan Universiti Bandar Sunway Subang Jaya Selangor', t.type_id, '47500', 1
FROM university_type t WHERE t.type_name = 'International';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 49, 'University of Nottingham Malaysia', 'Jalan Broga Semenyih Selangor', t.type_id, '43500', 1
FROM university_type t WHERE t.type_name = 'International';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 50, 'Curtin University Malaysia', 'Lot 13149 Block 5 Kuala Baram Land District CDT 250 Miri Sarawak', t.type_id, '98009', 1
FROM university_type t WHERE t.type_name = 'International';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 51, 'Swinburne University of Technology Sarawak Campus', 'Jalan Simpang Tiga Kuching Sarawak', t.type_id, '93350', 1
FROM university_type t WHERE t.type_name = 'International';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 52, 'Newcastle University Medicine Malaysia', '1 Jalan Sarjana 1 Kota Ilmu Educity@Iskandar Iskandar Puteri Johor', t.type_id, '79200', 1
FROM university_type t WHERE t.type_name = 'International';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 53, 'University of Southampton Malaysia', 'C0301 C0302 C0401 Blok C Eko Galleria Jalan Eko Botani 3 Taman Eko Botani Iskandar Puteri Johor', t.type_id, '79100', 1
FROM university_type t WHERE t.type_name = 'International';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 54, 'Heriot-Watt University Malaysia', 'No. 1 Jalan Venna P5/2 Precinct 5 Putrajaya', t.type_id, '62200', 1
FROM university_type t WHERE t.type_name = 'International';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 55, 'Xiamen University Malaysia', 'Jalan Sunsuria Bandar Sunsuria 43900 Sepang Selangor', t.type_id, '43900', 1
FROM university_type t WHERE t.type_name = 'International';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 56, 'Lincoln University College', 'Wisma Lincoln No. 12-18 Jalan SS 6/12 47301 Petaling Jaya Selangor Darul Ehsan', t.type_id, '47301', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 57, 'Han Chiang University College of Communication', 'George Town Penang', t.type_id, '10000', 1
FROM university_type t WHERE t.type_name = 'Private';

INSERT INTO university (university_id, university_name, address, type_id, postcode_id, is_active)
SELECT 58, 'Southern University College', 'PTD 64888 15KM Jalan Skudai P.O. Box 76 81300 Skudai Johor', t.type_id, '81300', 1
FROM university_type t WHERE t.type_name = 'Private';

