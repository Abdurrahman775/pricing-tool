#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  Header, Footer, AlignmentType, LevelFormat, HeadingLevel,
  BorderStyle, WidthType, ShadingType, PageNumber, PageBreak
} = require('docx');

// --- Color palette ---
const NAVY = '1a1a2e';
const PURPLE = '51459e';
const PURPLE_LIGHT = 'f4f0ff';
const PURPLE_DIM = 'e8e0ff';
const RED_LIGHT = 'fff3f3';
const RED_BORDER = 'f5c6c6';
const RED_TEXT = 'c0392b';
const GREEN = '059669';
const GRAY = '6b7280';
const GRAY_LIGHT = 'f3f4f6';
const WHITE = 'ffffff';

// --- Helpers ---
function border(color = 'CCCCCC', size = 1) {
  return { style: BorderStyle.SINGLE, size, color };
}
const cellBorders = {
  top: border(), bottom: border(), left: border(), right: border()
};
const cellMargins = { top: 80, bottom: 80, left: 120, right: 120 };

function headingCell(text, width, shading) {
  return new TableCell({
    borders: cellBorders,
    width: { size: width, type: WidthType.DXA },
    shading: { fill: shading || PURPLE, type: ShadingType.CLEAR },
    margins: cellMargins,
    verticalAlign: 'center',
    children: [new Paragraph({
      children: [new TextRun({ text, bold: true, color: WHITE, font: 'Arial', size: 20 })]
    })]
  });
}

function dataCell(text, width, shading) {
  return new TableCell({
    borders: cellBorders,
    width: { size: width, type: WidthType.DXA },
    shading: shading ? { fill: shading, type: ShadingType.CLEAR } : undefined,
    margins: cellMargins,
    children: [new Paragraph({
      children: [new TextRun({ text: String(text), font: 'Arial', size: 20 })]
    })]
  });
}

function emptyPara() {
  return new Paragraph({ spacing: { after: 80 } });
}

function heading1(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    spacing: { before: 360, after: 200 },
    children: [new TextRun({ text, bold: true, font: 'Arial', size: 28, color: NAVY })]
  });
}

function heading2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    spacing: { before: 280, after: 160 },
    children: [new TextRun({ text, bold: true, font: 'Arial', size: 24, color: PURPLE })]
  });
}

function bodyText(text) {
  return new Paragraph({
    spacing: { after: 120 },
    children: [new TextRun({ text, font: 'Arial', size: 21 })]
  });
}

function divider() {
  return new Paragraph({
    spacing: { before: 200, after: 200 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 6, color: PURPLE, space: 1 } }
  });
}

// --- Section builders ---
function buildCover(data) {
  const items = [
    { label: 'Project', value: data.project },
    { label: 'Client', value: data.client },
    { label: 'Package', value: data.package },
    { label: 'Price', value: `${data.currency}${String(data.price).replace(data.currency, '').trim()}` },
    { label: 'Valid Until', value: new Date(Date.now() + 30*86400000).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }
  ];
  const colW = Math.floor(9000 / 2);
  const rows = items.map(item => new TableRow({
    children: [
      new TableCell({
        borders: cellBorders,
        width: { size: colW, type: WidthType.DXA },
        shading: { fill: PURPLE_LIGHT, type: ShadingType.CLEAR },
        margins: cellMargins,
        children: [new Paragraph({ children: [new TextRun({ text: item.label, bold: true, font: 'Arial', size: 20, color: PURPLE })] })]
      }),
      new TableCell({
        borders: cellBorders,
        width: { size: colW, type: WidthType.DXA },
        margins: cellMargins,
        children: [new Paragraph({ children: [new TextRun({ text: item.value, font: 'Arial', size: 20 })] })]
      })
    ]
  }));

  return [
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { after: 200 },
      children: [new TextRun({ text: data.title || 'Proposal', bold: true, font: 'Arial', size: 40, color: NAVY })]
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { after: 80 },
      children: [new TextRun({ text: data.subtitle || '', font: 'Arial', size: 22, color: GRAY })]
    }),
    new Paragraph({
      alignment: AlignmentType.CENTER,
      spacing: { after: 400 },
      children: [new TextRun({ text: data.date || '', font: 'Arial', size: 20, color: GRAY })]
    }),
    divider(),
    new Table({
      width: { size: 9000, type: WidthType.DXA },
      columnWidths: [colW, colW],
      rows
    }),
    emptyPara()
  ];
}

function buildHighlight(content) {
  return new Paragraph({
    spacing: { before: 160, after: 160 },
    indent: { left: 360, right: 360 },
    border: {
      left: { style: BorderStyle.SINGLE, size: 12, color: PURPLE, space: 8 },
      top: border(PURPLE_LIGHT, 1), bottom: border(PURPLE_LIGHT, 1), right: border(PURPLE_LIGHT, 1)
    },
    shading: { fill: PURPLE_LIGHT, type: ShadingType.CLEAR },
    children: [new TextRun({ text: content, font: 'Arial', size: 21, italics: true, color: NAVY })]
  });
}

function buildWarning(data) {
  const items = (data.items || []).map(item => new Paragraph({
    numbering: { reference: 'bullets', level: 0 },
    spacing: { after: 60 },
    children: [new TextRun({ text: item, font: 'Arial', size: 20, color: '666666' })]
  }));
  return [
    divider(),
    new Paragraph({
      spacing: { before: 200, after: 120 },
      indent: { left: 200, right: 200 },
      shading: { fill: RED_LIGHT, type: ShadingType.CLEAR },
      border: {
        top: border(RED_BORDER, 2), bottom: border(RED_BORDER, 2),
        left: border(RED_BORDER, 2), right: border(RED_BORDER, 2)
      },
      children: [new TextRun({ text: data.title || 'Explicitly Excluded', bold: true, font: 'Arial', size: 22, color: RED_TEXT })]
    }),
    ...items
  ];
}

function buildList(style, items) {
  const ref = style === 'number' ? 'numbers' : 'bullets';
  return (items || []).map(item => new Paragraph({
    numbering: { reference: ref, level: 0 },
    spacing: { after: 60 },
    children: [new TextRun({ text: item, font: 'Arial', size: 21 })]
  }));
}

function buildTable(section) {
  if (!section.headers || !section.rows || section.rows.length === 0) return [emptyPara()];
  const cols = section.headers.length;
  const colW = Math.floor(9000 / cols);

  const headerRow = new TableRow({
    children: section.headers.map(h => headingCell(h, colW))
  });
  const dataRows = section.rows.map((row, ri) => new TableRow({
    children: row.map((cell, ci) => dataCell(cell, colW, ri % 2 === 0 ? undefined : GRAY_LIGHT))
  }));

  return [new Table({
    width: { size: 9000, type: WidthType.DXA },
    columnWidths: Array(cols).fill(colW),
    rows: [headerRow, ...dataRows]
  }), emptyPara()];
}

function buildSignature(section) {
  const colW = Math.floor(9000 / 2);
  return [
    emptyPara(),
    divider(),
    new Table({
      width: { size: 9000, type: WidthType.DXA },
      columnWidths: [colW, colW],
      rows: [new TableRow({
        children: [
          new TableCell({
            borders: { top: border('333333', 1) },
            width: { size: colW, type: WidthType.DXA },
            margins: { top: 200, bottom: 80, left: 120, right: 120 },
            children: [new Paragraph({
              alignment: AlignmentType.CENTER,
              children: [new TextRun({ text: section.clientLabel || 'Signature — Client', font: 'Arial', size: 18, color: GRAY, italics: true })]
            })]
          }),
          new TableCell({
            borders: { top: border('333333', 1) },
            width: { size: colW, type: WidthType.DXA },
            margins: { top: 200, bottom: 80, left: 120, right: 120 },
            children: [new Paragraph({
              alignment: AlignmentType.CENTER,
              children: [new TextRun({ text: section.providerLabel || 'Signature — Provider', font: 'Arial', size: 18, color: GRAY, italics: true })]
            })]
          })
        ]
      })]
    })
  ];
}

// --- Main builder ---
function buildDocument(content) {
  const title = content.title || 'Document';
  const subtitle = content.subtitle || '';
  const children = [];

  if (content.sections) {
    content.sections.forEach(section => {
      switch (section.type) {
        case 'cover':
          children.push(...buildCover({ ...section, title, subtitle, date: content.date }));
          break;
        case 'heading':
          children.push(section.level === 1 ? heading1(section.text) : heading2(section.text));
          break;
        case 'text':
          children.push(bodyText(section.content));
          break;
        case 'highlight':
          children.push(buildHighlight(section.content));
          break;
        case 'warning':
          children.push(...buildWarning(section));
          break;
        case 'list':
          children.push(...buildList(section.style, section.items));
          break;
        case 'table':
          children.push(...buildTable(section));
          break;
        case 'signature':
          children.push(...buildSignature(section));
          break;
      }
    });
  }

  return children;
}

// --- Main ---
function main() {
  const inputPath = process.argv[2];
  if (!inputPath) {
    console.error('Usage: node build_docx.js <input-json-path> [output-path]');
    process.exit(1);
  }

  const raw = fs.readFileSync(inputPath, 'utf-8');
  let content;
  try {
    content = JSON.parse(raw);
  } catch {
    console.error('Invalid JSON input');
    process.exit(1);
  }

  const children = buildDocument(content);

  const doc = new Document({
    styles: {
      default: {
        document: { run: { font: 'Arial', size: 21 } }
      },
      paragraphStyles: [
        {
          id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
          run: { size: 28, bold: true, font: 'Arial', color: NAVY },
          paragraph: { spacing: { before: 360, after: 200 }, outlineLevel: 0 }
        },
        {
          id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
          run: { size: 24, bold: true, font: 'Arial', color: PURPLE },
          paragraph: { spacing: { before: 280, after: 160 }, outlineLevel: 1 }
        }
      ]
    },
    numbering: {
      config: [
        {
          reference: 'bullets',
          levels: [{
            level: 0, format: LevelFormat.BULLET, text: '\u2022', alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 720, hanging: 360 } } }
          }]
        },
        {
          reference: 'numbers',
          levels: [{
            level: 0, format: LevelFormat.DECIMAL, text: '%1.', alignment: AlignmentType.LEFT,
            style: { paragraph: { indent: { left: 720, hanging: 360 } } }
          }]
        }
      ]
    },
    sections: [{
      properties: {
        page: {
          size: { width: 12240, height: 15840 },
          margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 }
        }
      },
      headers: {
        default: new Header({
          children: [new Paragraph({
            alignment: AlignmentType.RIGHT,
            children: [new TextRun({ text: content.project || '', font: 'Arial', size: 16, color: GRAY, italics: true })]
          })]
        })
      },
      footers: {
        default: new Footer({
          children: [new Paragraph({
            alignment: AlignmentType.CENTER,
            children: [
              new TextRun({ text: 'Page ', font: 'Arial', size: 16, color: GRAY }),
              new TextRun({ children: [PageNumber.CURRENT], font: 'Arial', size: 16, color: GRAY })
            ]
          })]
        })
      },
      children
    }]
  });

  const outputPath = process.argv[3] || inputPath.replace(/\.json$/, '.docx');
  Packer.toBuffer(doc).then(buffer => {
    fs.writeFileSync(outputPath, buffer);
    console.log(outputPath);
  });
}

main();
