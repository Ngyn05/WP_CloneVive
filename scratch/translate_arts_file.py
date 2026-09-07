import json
import re

file_path = 'footer-sites/arts.vive.com/us/index.htm'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Bang tu dien thay the text
dictionary = {
    "<title data-next-head=\"\">Home</title>": "<title data-next-head=\"\">VIVE Arts - Số hóa nghệ thuật & Văn hóa trong Thực tế ảo</title>",
    "Highlights": "Dự án Nổi bật",
    "ABOUT VIVE ARTS": "GIỚI THIỆU VỀ VIVE ARTS",
    "OUR GOALS AND OBJECTIVES": "MỤC TIÊU VÀ SỨ MỆNH CỦA CHÚNG TÔI",
    "VIVE ARTS&#x27; MISSION IS TO ENABLE EXPERIMENTATION IN PRESERVING CULTURAL HERITAGE FOR THE WORLD AND TO DEMOCRATIZE CREATION THROUGH DIGITAL INNOVATION IN THE ARTS.": "SỨ MỆNH CỦA VIVE ARTS LÀ THÚC ĐẨY THỬ NGHIỆM TRONG VIỆC BẢO TỒN DI SẢN VĂN HÓA CHO THẾ GIỚI VÀ PHỔ CẬP HÓA SÁNG TẠO NGHỆ THUẬT THÔNG QUA ĐỔI MỚI KỸ THUẬT SỐ.",
    "VIVE ARTS' MISSION IS TO ENABLE EXPERIMENTATION IN PRESERVING CULTURAL HERITAGE FOR THE WORLD AND TO DEMOCRATIZE CREATION THROUGH DIGITAL INNOVATION IN THE ARTS.": "SỨ MỆNH CỦA VIVE ARTS LÀ THÚC ĐẨY THỬ NGHIỆM TRONG VIỆC BẢO TỒN DI SẢN VĂN HÓA CHO THẾ GIỚI VÀ PHỔ CẬP HÓA SÁNG TẠO NGHỆ THUẬT THÔNG QUA ĐỔI MỚI KỸ THUẬT SỐ.",
    
    "Playing with Fire: An Immersive Odyssey with Yuja Wang": "Hành trình Thực tế ảo Đắm chìm cùng Nghệ sĩ Yuja Wang: Playing with Fire",
    "A Statue for Liberty": "Tượng Nữ thần Tự do: Bản giao hưởng Thực tế ảo",
    "The Metamorphoses of Guernica": "Sự Biến ảo của Kiệt tác Guernica",
    "Terracotta Warriors: Secrets of the First Emperor’s Mausoleum": "Đội quân Đất nung: Bí mật Lăng mộ Tần Thủy Hoàng",
    "Terracotta Warriors: Secrets of the First Emperorâ€™s Mausoleum": "Đội quân Đất nung: Bí mật Lăng mộ Tần Thủy Hoàng",
    "La Magie Opéra": "Phép màu Nhà hát Opéra Paris (La Magie Opéra)",
    "La Magie OpÃ©ra": "Phép màu Nhà hát Opéra Paris (La Magie Opéra)",
    "Versailles: Lost Gardens of the Sun King": "Cung điện Versailles: Khu vườn Thất lạc của Vua Mặt Trời",
    "Gaudí, the Atelier of the Divine": "Gaudí: Xưởng sáng tác của Đấng Tối Cao",
    "GaudÃ­, the Atelier of the Divine": "Gaudí: Xưởng sáng tác của Đấng Tối Cao",
    "Ryan Gander: Ryan Waiting": "Triển lãm Ryan Gander: Ryan Waiting",
    "Xavier Veilhan: Sculpture Park": "Công viên Điêu khắc Không gian ảo Xavier Veilhan",
    "Libby Heaney: Heartbreak & Magic": "Triển lãm Libby Heaney: Heartbreak & Magic",
    "Award-Winning VR Experience &quot;Le Bal de Paris de Blanca Li&quot; Comes to Taipei, Co-produced by VIVE Arts and Surprise Lab": "Trải nghiệm VR đoạt giải &quot;Le Bal de Paris của Blanca Li&quot; do VIVE Arts và Surprise Lab đồng sản xuất",
    "Award-Winning VR Experience \"Le Bal de Paris de Blanca Li\" Comes to Taipei, Co-produced by VIVE Arts and Surprise Lab": "Trải nghiệm VR đoạt giải \"Le Bal de Paris của Blanca Li\" do VIVE Arts và Surprise Lab đồng sản xuất",
    "Ben Elliot: Metaone": "Triển lãm Ben Elliot: Metaone",
    "Criptech Metaverse Lab": "Phòng thí nghiệm Không gian ảo Criptech Metaverse Lab",

    "We aim to broaden audience demographics for museums and cultural institutions and make art encounters accessible through technological innovation.": "Chúng tôi hướng tới việc mở rộng đối tượng khán giả cho các bảo tàng, tổ chức văn hóa và giúp mọi người dễ dàng tiếp cận các tác phẩm nghệ thuật thông qua đổi mới công nghệ.",
    "We augment existing practices in curating and storytelling to create new ways to interpret and preserve human heritage.": "Chúng tôi nâng tầm các phương pháp giám tuyển và kể chuyện hiện có để tạo ra những cách thức mới nhằm diễn giải và bảo tồn di sản nhân loại.",
    "We enable experimentation with and exploration of new art forms and experiences by harnessing the latest technologies across the entire art ecosystem.": "Chúng tôi tạo điều kiện thử nghiệm và khám phá các hình thức, trải nghiệm nghệ thuật mới bằng cách ứng dụng những công nghệ mới nhất trên toàn bộ hệ sinh thái nghệ thuật.",
    "We offer aesthetic and creative power to artists to address a diverse global audience, contributing to the knowledge and enjoyment of art and cultural heritage.": "Chúng tôi trao quyền thẩm mỹ và sáng tạo cho các nghệ sĩ để tiếp cận khán giả toàn cầu đa dạng, đóng góp vào tri thức và niềm đam mê thưởng thức nghệ thuật di sản.",

    "Privacy Contact:": "Liên hệ Quyền riêng tư:",
    "Privacy Contact": "Liên hệ Quyền riêng tư",
    "Get In Touch": "Liên hệ với chúng tôi",
    "Contact Us": "Liên hệ",
    "Contact us": "Liên hệ hỗ trợ",
    "Terms of Use": "Điều khoản sử dụng",
    "Legal Terms": "Điều khoản pháp lý",
    "Privacy policy": "Chính sách quyền riêng tư",
    "Privacy Policy": "Chính sách quyền riêng tư",
    "Cookie Policy": "Chính sách Cookie",
    "Cookie preferences": "Tùy chọn Cookie",
    "Do Not Sell or Share My Personal Information / Limit the Use of My Sensitive Personal Information": "Không bán hoặc chia sẻ thông tin cá nhân của tôi / Hạn chế sử dụng thông tin cá nhân nhạy cảm",
}

# Thay the doan van dai gioi thieu
old_intro_part1 = "VIVE Arts is committed to integrating ground-breaking art with cutting-edge technology, enabling artists to reinvent how we experience art and to build a richer digital art ecology. VIVE Arts aims to explore the limitless possibilities of VR, XR, blockchains and digital art in all its forms. VIVE Arts"
new_intro_part1 = "VIVE Arts cam kết kết hợp nghệ thuật đột phá với công nghệ tiên tiến, trao quyền cho các nghệ sĩ tái định hình cách chúng ta trải nghiệm nghệ thuật và xây dựng một hệ sinh thái nghệ thuật số phong phú hơn. VIVE Arts hướng tới việc khám phá những tiềm năng vô hạn của VR, XR, blockchain và nghệ thuật số dưới mọi hình thức. VIVE Arts "

old_intro_part2 = "provides comprehensive VR content licensing services, enabling institutions and organizations to bring immersive and captivating virtual reality experiences to audiences worldwide."
new_intro_part2 = "cung cấp dịch vụ cấp phép nội dung VR toàn diện, giúp các bảo tàng và tổ chức văn hóa mang đến những trải nghiệm thực tế ảo đắm chìm và lôi cuốn cho khán giả trên toàn thế giới."

content = content.replace(old_intro_part1, new_intro_part1)
content = content.replace(old_intro_part2, new_intro_part2)

for k, v in dictionary.items():
    content = content.replace(k, v)

# Lưu lại file
with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Translated arts.vive.com index.htm successfully!")
