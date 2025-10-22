package com.example.base.auth;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import java.nio.charset.StandardCharsets;
import java.time.Instant;
import java.util.Base64;
import java.util.HashMap;
import java.util.Map;
import java.util.logging.Logger;

/**
 * Lightweight HS256 JWT utility — stateless, no external libraries.
 * Supports signature validation, expiration, and claim extraction.
 */
public class JwtUtil {
    private static final String HMAC_ALGO = "HmacSHA256";
    private static final Logger LOGGER = Logger.getLogger(JwtUtil.class.getName());

    // Create a JWT token with subject (NIC), role, and expiry
    public static String createToken(String secret, String subject, String role, long expirySeconds) throws Exception {
        long now = Instant.now().getEpochSecond();
        long exp = now + expirySeconds;

        String headerJson = "{\"alg\":\"HS256\",\"typ\":\"JWT\"}";
        String payloadJson = String.format(
                "{\"sub\":\"%s\",\"role\":\"%s\",\"iat\":%d,\"exp\":%d}",
                escape(subject), escape(role), now, exp
        );

        String header = base64UrlEncode(headerJson.getBytes(StandardCharsets.UTF_8));
        String payload = base64UrlEncode(payloadJson.getBytes(StandardCharsets.UTF_8));

        String signingInput = header + "." + payload;
        String signature = base64UrlEncode(hmacSha256(secret, signingInput));

        return signingInput + "." + signature;
    }

    // Validate token and return claims map (null if invalid or expired)
    public static Map<String, String> validateToken(String secret, String token) {
        try {
            String[] parts = token.split("\\.");
            if (parts.length != 3) return null;

            String header = parts[0];
            String payload = parts[1];
            String signature = parts[2];

            // Verify signature
            String signingInput = header + "." + payload;
            String expectedSig = base64UrlEncode(hmacSha256(secret, signingInput));
            if (!constantTimeEquals(expectedSig, signature)) {
                LOGGER.warning("JWT signature mismatch");
                return null;
            }

            // Decode and parse payload
            String payloadJson = new String(base64UrlDecode(payload), StandardCharsets.UTF_8);
            Map<String, String> claims = parseSimpleJson(payloadJson);

            // Expiry check
            if (isExpired(claims)) {
                LOGGER.warning("JWT expired");
                return null;
            }

            return claims;
        } catch (Exception e) {
            LOGGER.warning("JWT validation failed: " + e.getMessage());
            return null;
        }
    }

    // Helper: check if token expired
    public static boolean isExpired(Map<String, String> claims) {
        if (claims == null || !claims.containsKey("exp")) return true;
        try {
            long exp = Long.parseLong(claims.get("exp"));
            long now = Instant.now().getEpochSecond();
            return now > exp;
        } catch (NumberFormatException e) {
            return true;
        }
    }

    // ------------------- Internal Helpers -------------------

    private static byte[] hmacSha256(String secret, String data) throws Exception {
        Mac mac = Mac.getInstance(HMAC_ALGO);
        mac.init(new SecretKeySpec(secret.getBytes(StandardCharsets.UTF_8), HMAC_ALGO));
        return mac.doFinal(data.getBytes(StandardCharsets.UTF_8));
    }

    private static String base64UrlEncode(byte[] input) {
        return Base64.getUrlEncoder().withoutPadding().encodeToString(input);
    }

    private static byte[] base64UrlDecode(String input) {
        return Base64.getUrlDecoder().decode(input);
    }

    private static Map<String, String> parseSimpleJson(String json) {
        Map<String, String> map = new HashMap<>();
        String s = json.trim();
        if (s.startsWith("{")) s = s.substring(1);
        if (s.endsWith("}")) s = s.substring(0, s.length() - 1);

        // split on commas not inside quotes
        String[] parts = s.split(",(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)");
        for (String part : parts) {
            String[] kv = part.split(":", 2);
            if (kv.length != 2) continue;
            String k = stripQuotes(kv[0].trim());
            String v = stripQuotes(kv[1].trim());
            map.put(k, v);
        }
        return map;
    }

    private static String stripQuotes(String s) {
        if (s.startsWith("\"") && s.endsWith("\"")) return s.substring(1, s.length() - 1);
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
