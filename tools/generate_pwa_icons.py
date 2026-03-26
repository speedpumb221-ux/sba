from PIL import Image, ImageDraw, ImageFont

# إعدادات الأيقونات
ICONS = [
    (192, 'icon-192.png'),
    (512, 'icon-512.png'),
]

BG_COLOR = (30, 64, 175)  # #1e40af
EMOJI = "🚗"
FONT_SIZE_RATIO = 0.6  # نسبة حجم الخط من حجم الصورة

# إعداد splash
SPLASH_SIZE = (640, 1136)
SPLASH_FILE = 'splash-640x1136.png'


def draw_centered_emoji(img, emoji, font_size):
    draw = ImageDraw.Draw(img)
    try:
        font = ImageFont.truetype("seguiemj.ttf", font_size)
    except:
        font = ImageFont.load_default()
    w, h = draw.textsize(emoji, font=font)
    x = (img.width - w) // 2
    y = (img.height - h) // 2
    draw.text((x, y), emoji, font=font, fill=(255, 255, 255))


def generate_icons():
    for size, filename in ICONS:
        img = Image.new('RGB', (size, size), BG_COLOR)
        draw_centered_emoji(img, EMOJI, int(size * FONT_SIZE_RATIO))
        img.save(filename)
        print(f"Generated {filename}")

def generate_splash():
    img = Image.new('RGB', SPLASH_SIZE, BG_COLOR)
    draw_centered_emoji(img, EMOJI, int(SPLASH_SIZE[0] * 0.4))
    img.save(SPLASH_FILE)
    print(f"Generated {SPLASH_FILE}")

if __name__ == "__main__":
    generate_icons()
    generate_splash()
