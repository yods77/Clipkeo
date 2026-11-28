class AppConfig {
  // Set this to the OAuth 2.0 Web Client ID using --dart-define.
  static const String googleServerClientId = String.fromEnvironment(
    'GOOGLE_SERVER_CLIENT_ID',
  );
  // 🔥 GANTI IP DI SINI SAJA (JIKA GANTI WIFI/MODEM) 🔥
  static const String ipAddress = "192.168.18.3";

  // URL Video Tutorial
  static const String videoTutorialUrl =
      "https://res.cloudinary.com/daopl1ji6/video/upload/v1770652956/tutorial_analytics_ifwcu9.mp4";

  // Jika API kamu juga di dalam folder yang sama, pastikan base API-nya juga benar
  static const String apiBaseUrl = "$baseUrl/clipsquad/public/api";

  // Port Laravel (Jangan diubah kecuali pakai port lain)
  static const String port = "8000";

  // URL untuk API (Login, Register, Data)
  static const String baseUrl = "http://$ipAddress:$port/api";

  // URL untuk Gambar/Storage (Tanpa /api)
  static const String rawBaseUrl = "http://$ipAddress:$port";

  // --- FUNGSI PINTAR PERBAIKI GAMBAR ---
  // Fungsi ini otomatis mengubah localhost/127.0.0.1 menjadi IP Laptop

  // Fungsi untuk memperbaiki URL gambar campaign
  static String fixImageUrl(String? url) {
    if (url == null || url.trim().isEmpty) return "";

    final value = url.trim().replaceAll(r'\', '/');

    // Tangani URL absolut
    if (value.startsWith("http://") || value.startsWith("https://")) {
      final uri = Uri.tryParse(value);

      if (uri == null) return "";

      const localHosts = {
        "localhost",
        "127.0.0.1",
        "clipsquad.test",
        "192.168.1.102",
        "192.168.1.110",
        "192.168.18.3",
      };

      if (localHosts.contains(uri.host.toLowerCase())) {
        return uri
            .replace(scheme: "http", host: ipAddress, port: int.parse(port))
            .toString();
      }

      // URL eksternal seperti Cloudinary tetap dipertahankan
      return uri.toString();
    }

    // Tangani URL relatif
    final path = value.replaceFirst(RegExp(r'^/+'), '');

    if (path.startsWith("storage/")) {
      return "$rawBaseUrl/$path";
    }

    return "$rawBaseUrl/storage/$path";
  }
}
