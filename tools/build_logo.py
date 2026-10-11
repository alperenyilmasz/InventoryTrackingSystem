from pathlib import Path
from PIL import Image

SRC = Path(__file__).resolve().parents[1] / "assets" / "img" / "logo-source.png"
OUT = Path(__file__).resolve().parents[1] / "assets" / "img" / "logo.png"
CREAM = (237, 232, 223, 255)


def main() -> None:
    img = Image.open(SRC).convert("RGBA")
    pixels = img.load()
    width, height = img.size
    out = Image.new("RGBA", (width, height), (0, 0, 0, 0))

    for y in range(height):
        for x in range(width):
            r, g, b, a = pixels[x, y]
            if a < 20:
                continue

            lum = 0.299 * r + 0.587 * g + 0.114 * b
            if lum < 18:
                continue

            out.putpixel((x, y), CREAM)

    out.save(OUT, optimize=True)
    print(f"Wrote {OUT}")


if __name__ == "__main__":
    main()
