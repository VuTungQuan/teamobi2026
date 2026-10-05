package nro.models.boss;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.List;
import nro.models.boss.Boss_Manager.BossManager;
import nro.models.consts.BossStatus;
import nro.models.data.LocalManager;
import nro.models.utils.Logger;

/**
 * Triệu hồi boss từ CMS (bảng boss_call). BossManager gọi BossCallDB.poll() trong vòng lặp update,
 * cứ 5 giây đọc bảng một lần: boss đang nghỉ thì cho ra ngay, không có thì tạo boss mới.
 */
public class BossCallDB {

    private static final long POLL_MS = 5_000;
    private static long lastPoll = 0;

    public static void poll() {
        if (System.currentTimeMillis() - lastPoll < POLL_MS) {
            return;
        }
        lastPoll = System.currentTimeMillis();
        List<Integer> done = new ArrayList<>();
        try (Connection con = LocalManager.getConnection()) {
            try (PreparedStatement ps = con.prepareStatement("SELECT id, boss_id FROM boss_call");
                    ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    summon(rs.getInt(2));
                    done.add(rs.getInt(1));
                }
            }
            try (PreparedStatement ps = con.prepareStatement("DELETE FROM boss_call WHERE id = ?")) {
                for (int id : done) {
                    ps.setInt(1, id);
                    ps.executeUpdate();
                }
            }
        } catch (Exception e) {
            System.err.println("BossCallDB: " + e.getMessage());
        }
    }

    private static void summon(int bossId) {
        for (Boss b : BossManager.gI().getBosses()) {
            if (b.id == bossId && b.bossStatus == BossStatus.REST) {
                b.lastTimeRest = 0; // hết thời gian nghỉ -> tick sau respawn
                Logger.log(Logger.YELLOW, "CMS triệu hồi boss " + b.name + " (" + bossId + ")\n");
                return;
            }
        }
        // ponytail: boss tạo thêm sẽ tồn tại tới khi restart server và tự hồi sinh theo chu kỳ như boss thường.
        Boss b = BossManager.gI().createBoss(bossId);
        Logger.log(Logger.YELLOW, b == null ? "CMS triệu hồi boss " + bossId + " thất bại: không có ID này\n"
                : "CMS tạo thêm boss " + b.name + " (" + bossId + ")\n");
    }
}
