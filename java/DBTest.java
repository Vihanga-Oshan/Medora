import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class DBTest {
    public static void main(String[] args) {
        String url = "jdbc:postgresql://ep-example-123456.us-east-2.aws.neon.tech/neondb?sslmode=require";
        String user = "neondb_owner";
        String password = "your_password_here";

        System.out.println("Testing connection to: " + url);
        try {
            Class.forName("org.postgresql.Driver");
            Connection conn = DriverManager.getConnection(url, user, password);
            if (conn != null) {
                System.out.println("SUCCESS: Connected to PostgreSQL (Neon)!");
                conn.close();
            }
        } catch (ClassNotFoundException e) {
            System.out.println("ERROR: Driver not found: " + e.getMessage());
        } catch (SQLException e) {
            System.out.println("ERROR: Connection failed: " + e.getMessage());
            e.printStackTrace();
        }
    }
}
