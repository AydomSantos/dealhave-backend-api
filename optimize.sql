
-- Adicionar índices para otimização
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE users ADD INDEX idx_rating (rating);
ALTER TABLE orders ADD INDEX idx_user_id (user_id);
ALTER TABLE orders ADD INDEX idx_created_at (created_at);
ALTER TABLE messages ADD INDEX idx_sender_receiver (sender_id, receiver_id);
ALTER TABLE messages ADD INDEX idx_sent_at (sent_at);
