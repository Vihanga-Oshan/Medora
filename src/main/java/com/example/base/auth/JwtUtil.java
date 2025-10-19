package com.example.base.auth;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import java.nio.charset.StandardCharsets;
import java.time.Instant;
import java.util.Base64;
import java.util.HashMap;
import java.util.Map;

// Minimal JWT utilities (HS256) without external libraries
public class JwtUtil {
    private static final String HMAC_ALGO = "HmacSHA256";

    // Create a JWT token with subject (nic) and role, expiry seconds from now
    public static String createToken(String secret, String subject, String role, long expirySeconds) throws Exception {
        long now = Instant.now().getEpochSecond();
        long exp = now + expirySeconds;

        String headerJson = "{\"alg\":\"HS256\",\"typ\":\"JWT\"}";
        String payloadJson = "{\"sub\":\"" + escape(subject) + "\",\"role\":\"" + escape(role) + "\",\"iat\":" + now + ",\"exp\":" + exp + "}";

        String header = base64UrlEncode(headerJson.getBytes(StandardCharsets.UTF_8));
        String payload = base64UrlEncode(payloadJson.getBytes(StandardCharsets.UTF_8));

        String signingInput = header + "." + payload;
        String signature = base64UrlEncode(hmacSha256(secret, signingInput));

        return signingInput + "." + signature;
    }

    // Validate token and return payload claims map (sub, role, iat, exp) or null if invalid/expired
    public static Map<String, String> validateToken(String secret, String token) {
        try {
            String[] parts = token.split("\\.");
            if (parts.length != 3) return null;
            String header = parts[0];
            String payload = parts[1];
            String signature = parts[2];

            String signingInput = header + "." + payload;
            byte[] expectedSig = hmacSha256(secret, signingInput);
            String expectedSigB64 = base64UrlEncode(expectedSig);
            if (!constantTimeEquals(expectedSigB64, signature)) return null;

            String payloadJson = new String(base64UrlDecode(payload), StandardCharsets.UTF_8);
            Map<String, String> claims = parseSimpleJson(payloadJson);

            // check exp
            String expS = claims.get("exp");
            if (expS == null) return null;
            long exp = Long.parseLong(expS);
            long now = Instant.now().getEpochSecond();
            if (now > exp) return null;

            return claims;
        } catch (Exception e) {
            return null;
        }
    }

    private static byte[] hmacSha256(String secret, String data) throws Exception {
        Mac mac = Mac.getInstance(HMAC_ALGO);
        SecretKeySpec keySpec = new SecretKeySpec(secret.getBytes(StandardCharsets.UTF_8), HMAC_ALGO);
        mac.init(keySpec);
        return mac.doFinal(data.getBytes(StandardCharsets.UTF_8));
    }

    private static String base64UrlEncode(byte[] input) {
        return Base64.getUrlEncoder().withoutPadding().encodeToString(input);
    }

    private static byte[] base64UrlDecode(String input) {
        return Base64.getUrlDecoder().decode(input);
    }

    // Very small JSON parser for flat string/number properties (not robust for nested or escaped strings beyond simple cases)
    private static Map<String, String> parseSimpleJson(String json) {
        Map<String, String> map = new HashMap<>();
        String s = json.trim();
        if (s.startsWith("{")) s = s.substring(1);
        if (s.endsWith("}")) s = s.substring(0, s.length()-1);
        String[] parts = s.split(",");
        for (String part : parts) {
            String[] kv = part.split(":", 2);
            if (kv.length != 2) continue;
            String k = kv[0].trim();
            String v = kv[1].trim();
            k = stripQuotes(k);
            v = stripQuotes(v);
            map.put(k, v);
        }
        return map;
    }

    private static String stripQuotes(String s) {
        if (s.startsWith("\"") && s.endsWith("\"")) return s.substring(1, s.length()-1);
        return s;
    }

    private static String escape(String s) {
        return s.replace("\\", "\\\\").replace("\"", "\\\"");
    }

    private static boolean constantTimeEquals(String a, String b) {
        if (a.length() != b.length()) return false;
        int result = 0;
        for (int i = 0; i < a.length(); i++) {
            result |= a.charAt(i) ^ b.charAt(i);
        }
        return result == 0;
    }
}

