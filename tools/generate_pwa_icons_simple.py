from PIL import Image

ICONS = [
    (192, 'public/icon-192.png'),
    (512, 'public/icon-512.png'),
]
SPLASH_SIZE = (640, 1136)
SPLASH_FILE = 'public/splash-640x1136.png'
BG_COLOR = (30, 64, 175)  # #1e40af


def generate_icons():
    for size, filename in ICONS:
        img = Image.new('RGB', (size, size), BG_COLOR)
        img.save(filename)
        print(f"Generated {filename}")

def generate_splash():
    img = Image.new('RGB', SPLASH_SIZE, BG_COLOR)
    img.save(SPLASH_FILE)
    print(f"Generated {SPLASH_FILE}")

if __name__ == "__main__":
    generate_icons()
    generate_splash()
