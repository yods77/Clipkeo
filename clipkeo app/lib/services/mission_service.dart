import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config.dart';

/*
|--------------------------------------------------------------------------
| MISSION SERVICE
|--------------------------------------------------------------------------
| Menangani pengambilan data riwayat submission dari API Laravel.
*/
class MissionService {
  final String baseUrl = AppConfig.baseUrl;

  Future<List<dynamic>> getMyMissions() async {
    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      String? token = prefs.getString('token');

      if (token == null || token.isEmpty) return [];

      final response = await http.get(
        Uri.parse('$baseUrl/my-submissions'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['data']; // Mengembalikan list submission
      } else {
        return [];
      }
    } catch (_) {
      return [];
    }
  }
}