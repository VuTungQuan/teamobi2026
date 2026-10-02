package nro.models.boss;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import nro.models.data.LocalManager;
import nro.models.map.ItemMap;
import nro.models.player.Player;
import nro.models.services.Service;
import nro.models.utils.Util;

/**
 * Phần thưởng diệt boss cấu hình từ CMS (bảng boss_reward).
 * Gọi BossRewardDB.give(boss, plKill) trong Boss.die(). Cache 60 giây nên sửa trong CMS
 * không cần khởi động lại server.
 */
public class BossRewardDB {

    private static final long RELOAD_MS = 60_000;
    private static long lastLoad = 0;
    private static Map<String, List<int[]>> cache = new HashMap<>(); // boss_name -> [item_id, qmin, qmax, ratio]

    private static synchronized void reload() {
        if (System.currentTimeMillis() - lastLoad < RELOAD_MS) {
            return;
        }
        Map<String, List<int[]>> m = new HashMap<>();
        try (Connection con = LocalManager.getConnection();
                PreparedStatement ps = con.prepareStatement("SELECT boss_name, item_id, quantity_min, quantity_max, ratio FROM boss_reward");
                ResultSet rs = ps.executeQuery()) {
            while (rs.next()) {
                m.computeIfAbsent(rs.getString(1), k -> new ArrayList<>())
                        .add(new int[]{rs.getInt(2), rs.getInt(3), rs.getInt(4), rs.getInt(5)});
            }
            cache = m;
        } catch (Exception e) {
            System.err.println("BossRewardDB: " + e.getMessage());
        }
        lastLoad = System.currentTimeMillis();
    }

    public static void give(Boss boss, Player plKill) {
        if (boss == null || plKill == null || plKill.isBot || boss.zone == null) {
            return;
        }
        reload();
        List<int[]> rewards = cache.get(boss.name);
        if (rewards == null) {
            return;
        }
        for (int[] r : rewards) {
            if (!Util.isTrue(r[3], 100)) {
                continue;
            }
            int qty = r[1] >= r[2] ? r[1] : Util.nextInt(r[1], r[2] + 1);
            Service.gI().dropItemMap(boss.zone, new ItemMap(boss.zone, r[0], qty,
                    boss.location.x, boss.zone.map.yPhysicInTop(boss.location.x, boss.location.y - 24), plKill.id));
        }
    }
}
