// Shrinks a photo in the browser before it is uploaded (ADR-018):
// - phone photos are often 3–8 MB; Vercel's proxy refuses uploads over about 4.5 MB,
// - smaller files upload faster on mobile data,
// - drawing the photo again drops hidden metadata such as the GPS location.

/** The longest side of an uploaded photo, in pixels. Cloudinary keeps at most this too. */
export const MAX_SIDE = 2000;

/** The size that fits inside MAX_SIDE × MAX_SIDE, keeping the shape. Never makes a photo bigger. */
export function fitWithin(width: number, height: number, maxSide = MAX_SIDE): { width: number; height: number } {
  const scale = Math.min(1, maxSide / Math.max(width, height));
  return { width: Math.round(width * scale), height: Math.round(height * scale) };
}

/** Draws the photo at most MAX_SIDE pixels wide or high and returns it as a good-quality JPEG. */
export async function resizeForUpload(file: File): Promise<Blob> {
  // createImageBitmap turns phone photos the right way up (EXIF orientation) on its own.
  const bitmap = await createImageBitmap(file);
  const { width, height } = fitWithin(bitmap.width, bitmap.height);

  const canvas = document.createElement("canvas");
  canvas.width = width;
  canvas.height = height;
  const context = canvas.getContext("2d");
  if (!context) throw new Error("This browser can't prepare photos for upload.");
  context.drawImage(bitmap, 0, 0, width, height);
  bitmap.close();

  return new Promise((resolve, reject) =>
    canvas.toBlob(
      (blob) => (blob ? resolve(blob) : reject(new Error("This photo could not be prepared for upload."))),
      "image/jpeg",
      0.85,
    ),
  );
}
