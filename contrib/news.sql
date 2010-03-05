CREATE TABLE "news" (
	"id" INTEGER PRIMARY KEY AUTOINCREMENT,
	"title" TEXT NOT NULL DEFAULT(''),
	"text" TEXT NOT NULL DEFAULT(''),
	"author" TEXT NOT NULL DEFAULT(''),
	"postedon" INTEGER NOT NULL DEFAULT(0),
	"editon" INTEGER NOT NULL DEFAULT(0)
);