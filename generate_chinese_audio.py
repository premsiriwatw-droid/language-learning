import asyncio
from pathlib import Path

import edge_tts


VOICE = "zh-CN-XiaoxiaoNeural"

AUDIO_ITEMS = [
    {
        "text": "你好",
        "output": "public/audio/chinese/greetings/ni-hao.mp3",
    },
    {
        "text": "老师",
        "output": "public/audio/chinese/self-introduction/lao-shi.mp3",
    },
    {
        "text": "五",
        "output": "public/audio/chinese/numbers/wu.mp3",
    },
]


async def generate_audio(text: str, output_path: str) -> None:
    path = Path(output_path)

    path.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    communicate = edge_tts.Communicate(
        text=text,
        voice=VOICE,
        rate="-5%",
        volume="+0%",
        pitch="+0Hz",
    )

    await communicate.save(str(path))

    print(
        f"สร้างเสียงสำเร็จ: {text} -> {path}"
    )


async def main() -> None:
    print("เริ่มสร้างไฟล์เสียงภาษาจีน...")
    print(f"Voice: {VOICE}")
    print()

    for item in AUDIO_ITEMS:
        await generate_audio(
            text=item["text"],
            output_path=item["output"],
        )

    print()
    print("สร้างไฟล์เสียงทั้งหมดเสร็จแล้ว")


if __name__ == "__main__":
    asyncio.run(main())